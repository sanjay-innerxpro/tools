<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\ScanAsset;
use App\Services\AssetDetectorService;
use App\Services\AssetEnricherService;
use App\Services\HeadlessBrowserService;
use App\Services\PageCrawlerService;
use App\Services\YtDlpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;
    public int $tries = 1;

    public function __construct(
        private string $scanId
    ) {}

    public function handle(
        PageCrawlerService $crawler,
        AssetDetectorService $detector,
        AssetEnricherService $enricher,
        HeadlessBrowserService $headlessBrowser,
        YtDlpService $ytDlp,
    ): void {
        $scan = Scan::find($this->scanId);

        if (!$scan || $scan->status !== 'queued') {
            return;
        }

        $scan->markProcessing();
        $this->updateProgress($scan->id, 'fetching', 10, 'Fetching page content...');

        try {
            $options = $scan->options ?? [];
            $isKnownPlatform = $this->isKnownVideoPlatform($scan->url);
            $fetchResult = $crawler->fetch($scan->url);

            if (!$fetchResult['success']) {
                // For known video platforms, don't bail — yt-dlp can handle them without HTML
                if (!$isKnownPlatform) {
                    $scan->markFailed($fetchResult['error_code'], $fetchResult['error_message']);
                    $this->updateProgress($scan->id, 'failed', 100, $fetchResult['error_message']);
                    return;
                }

                // Fake a minimal fetch result so the rest of the pipeline doesn't break
                $fetchResult = [
                    'success' => true,
                    'type' => 'html',
                    'url' => $scan->url,
                    'resolved_url' => $scan->url,
                    'html' => '',
                    'needs_js_render' => false,
                    'page_title' => null,
                ];
            }

            $scan->update([
                'resolved_url' => $fetchResult['resolved_url'] ?? $scan->url,
                'page_title' => $fetchResult['page_title'] ?? null,
            ]);

            $this->updateProgress($scan->id, 'detecting', 40, 'Scanning for media assets...');

            $assets = [];

            $needsJsRender = $fetchResult['needs_js_render'] ?? false;

            // Phase 0: For known video platforms, try yt-dlp FIRST — it's the most reliable extractor
            // and avoids false positives from HTML/headless detection picking up third-party URLs.
            if ($isKnownPlatform) {
                $this->updateProgress($scan->id, 'detecting', 45, 'Detected known video platform — using yt-dlp extractor...');

                $ytResult = $ytDlp->extract($scan->url);

                if ($ytResult['success'] && !empty($ytResult['assets'])) {
                    $assets = $ytResult['assets'];

                    if (!empty($ytResult['title']) && empty($scan->page_title)) {
                        $scan->update(['page_title' => $ytResult['title']]);
                    }
                }
            }

            $hasRealMedia = !empty(array_filter($assets, fn($a) => in_array($a['type'] ?? '', ['video', 'audio', 'document'])));

            // Phase 1: Static HTML detection (skip if yt-dlp already succeeded)
            if (!$hasRealMedia) {
                if ($fetchResult['type'] === 'direct_media') {
                    $assets = $detector->detectDirectMedia(
                        $fetchResult['url'],
                        $fetchResult['content_type'],
                        $fetchResult['content_length'] ?? null,
                        $fetchResult['content_disposition'] ?? null,
                    );
                } else {
                    $assets = $detector->detect(
                        $fetchResult['html'],
                        $fetchResult['resolved_url'],
                        $options,
                    );
                }
            }

            // Phase 2: If page needs JS rendering and Phase 1 found no *real* media, use headless browser
            // Real media = video/audio/document. Iframes of unknown type don't count.
            $hasRealMedia = !empty(array_filter($assets, fn($a) => in_array($a['type'] ?? '', ['video', 'audio', 'document'])));
            if (!$hasRealMedia && $needsJsRender) {
                $this->updateProgress($scan->id, 'detecting', 50, 'Page uses JavaScript — launching headless browser...');

                $renderResult = $headlessBrowser->render($scan->url);

                if ($renderResult['success']) {
                    // Re-detect from rendered HTML
                    if (!empty($renderResult['html'])) {
                        $assets = $detector->detect(
                            $renderResult['html'],
                            $fetchResult['resolved_url'],
                            $options,
                        );
                    }

                    // Add intercepted network media URLs
                    foreach ($renderResult['intercepted_urls'] as $intercepted) {
                        $interceptedUrl = $intercepted['url'] ?? '';
                        if (empty($interceptedUrl)) continue;

                        $extension = strtolower(pathinfo(parse_url($interceptedUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                        $contentType = $intercepted['content_type'] ?? '';
                        $type = $this->classifyMediaUrl($extension, $contentType);

                        if ($type) {
                            $assets[] = [
                                'url' => $interceptedUrl,
                                'filename' => basename(parse_url($interceptedUrl, PHP_URL_PATH) ?? 'unknown'),
                                'type' => $type,
                                'mime_type' => $contentType ?: null,
                                'extension' => $extension ?: null,
                                'file_size' => null,
                                'source' => 'network-intercept',
                                'is_drm' => false,
                                'is_downloadable' => true,
                            ];
                        }
                    }

                    // Deduplicate combined results
                    $seen = [];
                    $assets = array_filter($assets, function ($asset) use (&$seen) {
                        $key = $asset['url'];
                        if (isset($seen[$key])) return false;
                        $seen[$key] = true;
                        return true;
                    });
                    $assets = array_values($assets);
                }
            }

            $hasRealMedia = !empty(array_filter($assets, fn($a) => in_array($a['type'] ?? '', ['video', 'audio', 'document'])));
            if (!$hasRealMedia) {
                // Phase 3: Try yt-dlp as final fallback for non-known sites (supports 1800+ sites)
                $this->updateProgress($scan->id, 'detecting', 65, 'Trying yt-dlp extractor (supports YouTube, Vimeo, Wistia, 1800+ sites)...');

                $ytResult = $ytDlp->extract($scan->url);

                if ($ytResult['success'] && !empty($ytResult['assets'])) {
                    $assets = $ytResult['assets'];

                    // Update page title if yt-dlp found one
                    if (!empty($ytResult['title']) && empty($scan->page_title)) {
                        $scan->update(['page_title' => $ytResult['title']]);
                    }
                }
            }

            // Strip non-media iframes and blob: URLs before final check and saving
            $assets = array_values(array_filter($assets, function ($a) {
                if ($a['type'] === 'other') return false;
                if (str_starts_with($a['url'] ?? '', 'blob:')) return false;
                return true;
            }));

            if (empty($assets)) {
                $message = 'No downloadable media was found on this page.';
                if ($needsJsRender) {
                    $message = 'No media found. This page uses JavaScript to load content dynamically — the headless browser and yt-dlp extractor also found nothing.';
                }
                $scan->markCompleted();
                $scan->update(['options' => array_merge($options, ['needs_js_render' => $needsJsRender])]);
                $this->updateProgress($scan->id, 'done', 100, $message);
                return;
            }

            $this->updateProgress($scan->id, 'enriching', 70, 'Analyzing ' . count($assets) . ' detected assets...');

            // Enrich assets with file sizes and MIME types
            $assets = $enricher->enrichAll($assets);

            // Parse HLS manifests for quality variants
            foreach ($assets as &$asset) {
                if (($asset['extension'] ?? '') === 'm3u8' || ($asset['mime_type'] ?? '') === 'application/x-mpegURL') {
                    $variants = $enricher->parseHlsManifest($asset['url']);
                    if (!empty($variants)) {
                        $asset['quality_variants'] = $variants;
                        $asset['quality'] = $variants[0]['resolution'] ?? $variants[0]['label'] ?? null;
                    }
                }
            }
            unset($asset);

            // Apply minimum file size filter
            // Never filter streaming manifests (m3u8/mpd) — they're tiny files that represent full videos
            $streamingExts = ['m3u8', 'mpd'];
            $minSize = $options['minFileSize'] ?? 5120;
            $assets = array_filter($assets, function ($asset) use ($minSize, $streamingExts) {
                if ($asset['file_size'] === null) return true; // Keep unknowns
                if (in_array($asset['extension'] ?? '', $streamingExts)) return true; // Always keep manifests
                if (in_array($asset['source'] ?? '', ['network-intercept', 'hls-manifest', 'dash-manifest'])) return true;
                return $asset['file_size'] >= $minSize;
            });

            // Apply max results limit
            $maxResults = $options['maxResults'] ?? 100;
            $assets = array_slice(array_values($assets), 0, $maxResults);

            $this->updateProgress($scan->id, 'saving', 90, 'Saving results...');

            // Store assets in database
            foreach ($assets as $assetData) {
                ScanAsset::create([
                    'scan_id' => $scan->id,
                    'url' => $assetData['url'],
                    'filename' => $assetData['filename'] ?? null,
                    'type' => $assetData['type'] ?? 'other',
                    'mime_type' => $assetData['mime_type'] ?? null,
                    'extension' => $assetData['extension'] ?? null,
                    'file_size' => $assetData['file_size'] ?? null,
                    'quality' => $assetData['quality'] ?? null,
                    'quality_variants' => $assetData['quality_variants'] ?? null,
                    'is_drm' => $assetData['is_drm'] ?? false,
                    'is_downloadable' => $assetData['is_downloadable'] ?? true,
                    'source' => $assetData['source'] ?? null,
                ]);
            }

            $scan->markCompleted();
            $this->updateProgress($scan->id, 'done', 100, 'Scan complete. Found ' . count($assets) . ' assets.');

        } catch (\Exception $e) {
            Log::error('Scan processing failed', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $scan->markFailed('INTERNAL_ERROR', 'Something went wrong on our end. Please try again.');
            $this->updateProgress($scan->id, 'failed', 100, 'Something went wrong on our end. Please try again.');
        }
    }

    private function updateProgress(string $scanId, string $phase, int $progress, string $message): void
    {
        Cache::put("scan_progress:{$scanId}", [
            'phase' => $phase,
            'progress' => $progress,
            'message' => $message,
            'updated_at' => now()->toIso8601String(),
        ], 300);
    }

    private function classifyMediaUrl(string $extension, string $contentType): ?string
    {
        // 'ts' excluded intentionally — individual HLS segments aren't useful; m3u8 manifest covers the stream
        $videoExts = ['mp4', 'webm', 'ogg', 'ogv', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'm3u8', 'mpd'];
        $audioExts = ['mp3', 'wav', 'aac', 'flac', 'm4a', 'opus', 'oga', 'wma'];
        $docExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', '7z'];

        if (in_array($extension, $videoExts)) return 'video';
        if (in_array($extension, $audioExts)) return 'audio';
        if (in_array($extension, $docExts)) return 'document';

        $ct = strtolower($contentType);
        if (str_starts_with($ct, 'video/')) return 'video';
        if (str_starts_with($ct, 'audio/')) return 'audio';
        if (str_contains($ct, 'pdf') || str_contains($ct, 'zip')) return 'document';
        if (str_starts_with($ct, 'application/x-mpeguRL') || str_starts_with($ct, 'application/dash+xml')) return 'video';

        // HLS/DASH by content-type variants
        if (str_contains($ct, 'mpegurl') || str_contains($ct, 'mpegURL')) return 'video';

        return null;
    }

    private function isKnownVideoPlatform(string $url): bool
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        $platforms = [
            'youtube.com', 'youtu.be', 'www.youtube.com', 'm.youtube.com',
            'vimeo.com', 'player.vimeo.com',
            'dailymotion.com', 'dai.ly',
            'twitch.tv', 'clips.twitch.tv',
            'facebook.com', 'fb.watch',
            'instagram.com',
            'tiktok.com',
            'twitter.com', 'x.com',
            'soundcloud.com',
            'bandcamp.com',
            'reddit.com',
            'streamable.com',
            'rumble.com',
            'bitchute.com',
            'odysee.com',
            'bilibili.com',
            'nicovideo.jp',
        ];

        foreach ($platforms as $platform) {
            if ($host === $platform || str_ends_with($host, '.' . $platform)) {
                return true;
            }
        }

        return false;
    }
}
