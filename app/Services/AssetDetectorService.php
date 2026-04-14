<?php

namespace App\Services;

use Illuminate\Support\Str;

class AssetDetectorService
{
    private const VIDEO_EXTENSIONS = ['mp4', 'webm', 'ogg', 'ogv', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'm3u8', 'mpd'];
    private const AUDIO_EXTENSIONS = ['mp3', 'wav', 'ogg', 'oga', 'aac', 'flac', 'm4a', 'wma', 'opus'];
    private const DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', '7z', 'csv', 'txt', 'epub', 'rtf'];
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'tiff'];

    private const AD_DOMAINS = [
        'doubleclick.net', 'googlesyndication.com', 'googleadservices.com',
        'google-analytics.com', 'googletagmanager.com', 'facebook.com/tr',
        'connect.facebook.net', 'analytics.', 'ads.', 'tracking.', 'pixel.',
        'hotjar.com', 'clarity.ms', 'segment.io', 'mixpanel.com',
    ];

    public function detect(string $html, string $baseUrl, array $options = []): array
    {
        $assets = [];
        $includeImages = $options['includeImages'] ?? false;
        $minFileSize = $options['minFileSize'] ?? 5120;

        // Extract from various sources
        $assets = array_merge(
            $assets,
            $this->detectVideoTags($html, $baseUrl),
            $this->detectAudioTags($html, $baseUrl),
            $this->detectLinks($html, $baseUrl),
            $this->detectMetaTags($html, $baseUrl),
            $this->detectJsonLd($html, $baseUrl),
            $this->detectStreamingManifests($html, $baseUrl),
            $this->detectInlineJsMediaUrls($html, $baseUrl),
            $this->detectIframeSources($html, $baseUrl),
        );

        if ($includeImages) {
            $assets = array_merge($assets, $this->detectImages($html, $baseUrl));
        }

        // Deduplicate by URL
        $assets = $this->deduplicate($assets);

        // Filter out ad/tracking assets
        $assets = array_filter($assets, fn($a) => !$this->isAdDomain($a['url']));

        return array_values($assets);
    }

    public function detectDirectMedia(string $url, string $contentType, ?string $contentLength, ?string $contentDisposition): array
    {
        $filename = $this->extractFilenameFromUrl($url);
        if ($contentDisposition && preg_match('/filename[^;=\n]*=(["\']?)(.+?)\1(?:;|$)/i', $contentDisposition, $m)) {
            $filename = $m[2];
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: $this->extensionFromMime($contentType);
        $type = $this->classifyByMime($contentType) ?: $this->classifyByExtension($extension);

        return [[
            'url' => $url,
            'filename' => $filename,
            'type' => $type ?? 'other',
            'mime_type' => $contentType,
            'extension' => strtolower($extension),
            'file_size' => $contentLength ? (int) $contentLength : null,
            'source' => 'direct_url',
            'is_drm' => false,
            'is_downloadable' => true,
        ]];
    }

    private function detectVideoTags(string $html, string $baseUrl): array
    {
        $assets = [];

        // <video src="...">
        if (preg_match_all('/<video[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $assets[] = $this->buildAsset($src, $baseUrl, 'video', 'video-tag');
            }
        }

        // <video> with data-src (lazy load)
        if (preg_match_all('/<video[^>]*\bdata-src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $assets[] = $this->buildAsset($src, $baseUrl, 'video', 'video-tag-lazy');
            }
        }

        // <source> inside video
        if (preg_match_all('/<video[^>]*>(.*?)<\/video>/si', $html, $videoBlocks)) {
            foreach ($videoBlocks[1] as $block) {
                if (preg_match_all('/<source[^>]*\bsrc=["\']([^"\']+)["\'][^>]*/i', $block, $sources)) {
                    foreach ($sources[1] as $src) {
                        $type = null;
                        if (preg_match('/type=["\']([^"\']+)["\']/i', $block, $typeMatch)) {
                            $type = $typeMatch[1];
                        }
                        $asset = $this->buildAsset($src, $baseUrl, 'video', 'video-source');
                        if ($type) {
                            $asset['mime_type'] = $type;
                        }
                        $assets[] = $asset;
                    }
                }
            }
        }

        return array_filter($assets);
    }

    private function detectAudioTags(string $html, string $baseUrl): array
    {
        $assets = [];

        // <audio src="...">
        if (preg_match_all('/<audio[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $assets[] = $this->buildAsset($src, $baseUrl, 'audio', 'audio-tag');
            }
        }

        // <source> inside audio
        if (preg_match_all('/<audio[^>]*>(.*?)<\/audio>/si', $html, $audioBlocks)) {
            foreach ($audioBlocks[1] as $block) {
                if (preg_match_all('/<source[^>]*\bsrc=["\']([^"\']+)["\'][^>]*/i', $block, $sources)) {
                    foreach ($sources[1] as $src) {
                        $assets[] = $this->buildAsset($src, $baseUrl, 'audio', 'audio-source');
                    }
                }
            }
        }

        return array_filter($assets);
    }

    private function detectLinks(string $html, string $baseUrl): array
    {
        $assets = [];

        if (preg_match_all('/<a[^>]*\bhref=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $href) {
                $extension = strtolower(pathinfo(parse_url($href, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

                if (in_array($extension, self::DOCUMENT_EXTENSIONS, true)) {
                    $assets[] = $this->buildAsset($href, $baseUrl, 'document', 'link');
                } elseif (in_array($extension, self::VIDEO_EXTENSIONS, true)) {
                    $assets[] = $this->buildAsset($href, $baseUrl, 'video', 'link');
                } elseif (in_array($extension, self::AUDIO_EXTENSIONS, true)) {
                    $assets[] = $this->buildAsset($href, $baseUrl, 'audio', 'link');
                }
            }
        }

        return array_filter($assets);
    }

    private function detectMetaTags(string $html, string $baseUrl): array
    {
        $assets = [];

        $ogMappings = [
            'og:video' => 'video',
            'og:video:url' => 'video',
            'og:video:secure_url' => 'video',
            'og:audio' => 'audio',
            'og:audio:url' => 'audio',
            'og:audio:secure_url' => 'audio',
        ];

        foreach ($ogMappings as $property => $type) {
            if (preg_match('/<meta[^>]*property=["\']' . preg_quote($property, '/') . '["\'][^>]*content=["\']([^"\']+)["\'][^>]*>/i', $html, $match)) {
                $assets[] = $this->buildAsset($match[1], $baseUrl, $type, 'og-meta');
            }
            // Also handle content before property
            if (preg_match('/<meta[^>]*content=["\']([^"\']+)["\'][^>]*property=["\']' . preg_quote($property, '/') . '["\'][^>]*>/i', $html, $match)) {
                $assets[] = $this->buildAsset($match[1], $baseUrl, $type, 'og-meta');
            }
        }

        return array_filter($assets);
    }

    private function detectJsonLd(string $html, string $baseUrl): array
    {
        $assets = [];

        if (preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $matches)) {
            foreach ($matches[1] as $jsonText) {
                $data = @json_decode($jsonText, true);
                if (!$data) continue;

                $items = isset($data['@graph']) ? $data['@graph'] : [$data];

                foreach ($items as $item) {
                    $itemType = $item['@type'] ?? '';

                    if ($itemType === 'VideoObject') {
                        if (!empty($item['contentUrl'])) {
                            $assets[] = $this->buildAsset($item['contentUrl'], $baseUrl, 'video', 'json-ld');
                        }
                        if (!empty($item['embedUrl'])) {
                            $assets[] = $this->buildAsset($item['embedUrl'], $baseUrl, 'video', 'json-ld-embed');
                        }
                    }

                    if ($itemType === 'AudioObject') {
                        if (!empty($item['contentUrl'])) {
                            $assets[] = $this->buildAsset($item['contentUrl'], $baseUrl, 'audio', 'json-ld');
                        }
                    }
                }
            }
        }

        return array_filter($assets);
    }

    private function detectStreamingManifests(string $html, string $baseUrl): array
    {
        $assets = [];

        // HLS .m3u8
        if (preg_match_all('/["\']([^"\']*\.m3u8[^"\']*)["\']/', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $asset = $this->buildAsset($src, $baseUrl, 'video', 'hls-manifest');
                if ($asset) {
                    $asset['mime_type'] = 'application/x-mpegURL';
                    $assets[] = $asset;
                }
            }
        }

        // DASH .mpd
        if (preg_match_all('/["\']([^"\']*\.mpd[^"\']*)["\']/', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $asset = $this->buildAsset($src, $baseUrl, 'video', 'dash-manifest');
                if ($asset) {
                    $asset['mime_type'] = 'application/dash+xml';
                    $assets[] = $asset;
                }
            }
        }

        return array_filter($assets);
    }

    private function detectInlineJsMediaUrls(string $html, string $baseUrl): array
    {
        $assets = [];

        // Build a combined regex for all media extensions
        $allMediaExts = array_merge(self::VIDEO_EXTENSIONS, self::AUDIO_EXTENSIONS, self::DOCUMENT_EXTENSIONS);
        $extPattern = implode('|', array_map('preg_quote', $allMediaExts));

        // Find full URLs with media extensions anywhere in the source (inside scripts, JSON, inline JS)
        $pattern = '/(https?:\/\/[^\s"\'<>\)\}\\\\]+\.(?:' . $extPattern . ')(?:\?[^\s"\'<>\)\}\\\\]*)?)/i';
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[1] as $url) {
                // Clean trailing punctuation
                $url = rtrim($url, '.,;:');
                $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                $type = $this->classifyByExtension($extension) ?? 'other';

                $asset = $this->buildAsset($url, $baseUrl, $type, 'inline-js');
                if ($asset) {
                    $assets[] = $asset;
                }
            }
        }

        // Look for CDN patterns common in video platforms (cloudfront, akamai, etc.)
        $cdnPatterns = [
            '/(https?:\/\/[^\s"\']*?(?:cloudfront|akamaized|cdn|media|stream|video|vod)[^\s"\']*?\.(?:mp4|m3u8|mpd|webm)[^\s"\']*)/i',
            '/(https?:\/\/[^\s"\']*?(?:\.mp4|\.m3u8|\.mpd|\.webm)(?:\?[^\s"\'<>]*)?)/i',
        ];

        foreach ($cdnPatterns as $cdnPattern) {
            if (preg_match_all($cdnPattern, $html, $cdnMatches)) {
                foreach ($cdnMatches[1] as $url) {
                    $url = rtrim($url, '.,;:)\'"');
                    $asset = $this->buildAsset($url, $baseUrl, 'video', 'cdn-detect');
                    if ($asset) {
                        $assets[] = $asset;
                    }
                }
            }
        }

        return array_filter($assets);
    }

    private function detectIframeSources(string $html, string $baseUrl): array
    {
        $assets = [];

        // Known embed providers
        $embedProviders = [
            'youtube.com/embed' => 'video',
            'player.vimeo.com' => 'video',
            'dailymotion.com/embed' => 'video',
            'soundcloud.com' => 'audio',
            'open.spotify.com/embed' => 'audio',
            'bandcamp.com/EmbeddedPlayer' => 'audio',
            'player.twitch.tv' => 'video',
            'facebook.com/plugins/video' => 'video',
            'tiktok.com/embed' => 'video',
        ];

        if (preg_match_all('/<iframe[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $url = $this->resolveUrl($src, $baseUrl);
                if (!$url) continue;

                $type = 'other';
                $source = 'iframe-unknown';

                foreach ($embedProviders as $domain => $mediaType) {
                    if (stripos($url, $domain) !== false) {
                        $type = $mediaType;
                        $source = 'iframe-' . explode('.', explode('/', $domain)[0])[0];
                        break;
                    }
                }

                $assets[] = [
                    'url' => $url,
                    'filename' => $this->extractFilenameFromUrl($url),
                    'type' => $type,
                    'mime_type' => null,
                    'extension' => null,
                    'file_size' => null,
                    'source' => $source,
                    'is_drm' => false,
                    'is_downloadable' => $type !== 'other', // Only downloadable if we know the type
                ];
            }
        }

        return array_filter($assets);
    }

    private function detectImages(string $html, string $baseUrl): array
    {
        $assets = [];

        // <img src="...">
        if (preg_match_all('/<img[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                // Skip tiny tracking pixels by checking for common patterns
                if (preg_match('/\b(1x1|pixel|track|beacon)\b/i', $src)) {
                    continue;
                }
                $assets[] = $this->buildAsset($src, $baseUrl, 'image', 'img-tag');
            }
        }

        // <img data-src="..."> (lazy load)
        if (preg_match_all('/<img[^>]*\bdata-src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $assets[] = $this->buildAsset($src, $baseUrl, 'image', 'img-tag-lazy');
            }
        }

        return array_filter($assets);
    }

    private function buildAsset(string $src, string $baseUrl, string $type, string $source): ?array
    {
        // Reject blob: and data: before URL resolution
        $trimmed = trim($src);
        if (str_starts_with($trimmed, 'blob:') || str_starts_with($trimmed, 'data:')) {
            return null;
        }

        $url = $this->resolveUrl($src, $baseUrl);
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Skip data: and blob: URIs — these are transient browser-local objects
        if (str_starts_with($url, 'data:') || str_starts_with($url, 'blob:')) {
            return null;
        }

        $filename = $this->extractFilenameFromUrl($url);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return [
            'url' => $url,
            'filename' => $filename,
            'type' => $type,
            'mime_type' => null,
            'extension' => $extension ?: null,
            'file_size' => null,
            'source' => $source,
            'is_drm' => false,
            'is_downloadable' => true,
        ];
    }

    private function resolveUrl(string $src, string $baseUrl): ?string
    {
        $src = trim($src);

        if (str_starts_with($src, '//')) {
            $baseScheme = parse_url($baseUrl, PHP_URL_SCHEME) ?? 'https';
            return $baseScheme . ':' . $src;
        }

        if (preg_match('/^https?:\/\//i', $src)) {
            return $src;
        }

        if (str_starts_with($src, '/')) {
            $parsed = parse_url($baseUrl);
            $base = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
            if (isset($parsed['port'])) {
                $base .= ':' . $parsed['port'];
            }
            return $base . $src;
        }

        // Relative URL
        $baseDir = preg_replace('/[^\/]*$/', '', $baseUrl);
        return $baseDir . $src;
    }

    private function extractFilenameFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $filename = $path ? basename($path) : 'unknown';

        // URL decode
        $filename = urldecode($filename);

        // Sanitize filename
        $filename = preg_replace('/[^\w\.\-]/', '_', $filename);

        return $filename ?: 'unknown';
    }

    private function classifyByMime(string $mime): ?string
    {
        $mime = strtolower($mime);
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_contains($mime, 'pdf') || str_contains($mime, 'zip') || str_contains($mime, 'document')) return 'document';
        return null;
    }

    private function classifyByExtension(string $ext): ?string
    {
        $ext = strtolower($ext);
        if (in_array($ext, self::VIDEO_EXTENSIONS)) return 'video';
        if (in_array($ext, self::AUDIO_EXTENSIONS)) return 'audio';
        if (in_array($ext, self::DOCUMENT_EXTENSIONS)) return 'document';
        if (in_array($ext, self::IMAGE_EXTENSIONS)) return 'image';
        return null;
    }

    private function extensionFromMime(string $mime): string
    {
        $map = [
            'video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/ogg' => 'ogv',
            'audio/mpeg' => 'mp3', 'audio/wav' => 'wav', 'audio/ogg' => 'ogg',
            'application/pdf' => 'pdf', 'application/zip' => 'zip',
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif',
        ];

        return $map[strtolower(explode(';', $mime)[0])] ?? '';
    }

    private function deduplicate(array $assets): array
    {
        $seen = [];
        $result = [];

        foreach ($assets as $asset) {
            $key = $asset['url'] ?? '';
            if (!empty($key) && !isset($seen[$key])) {
                $seen[$key] = true;
                $result[] = $asset;
            }
        }

        return $result;
    }

    private function isAdDomain(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return false;

        foreach (self::AD_DOMAINS as $ad) {
            if (str_contains($host, $ad)) {
                return true;
            }
        }

        return false;
    }
}
