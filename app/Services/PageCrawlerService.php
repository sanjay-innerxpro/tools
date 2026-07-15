<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Log;

class PageCrawlerService
{
    private Client $client;
    private UrlValidatorService $urlValidator;

    public function __construct(UrlValidatorService $urlValidator)
    {
        $this->urlValidator = $urlValidator;
        $this->client = new Client([
            'timeout' => 15,
            'connect_timeout' => 10,
            'allow_redirects' => [
                'max' => 5,
                'on_redirect' => function ($request, $response, $uri) {
                    $target = (string) $uri;
                    if (!$this->urlValidator->validateRedirectTarget($target)) {
                        throw new \RuntimeException('Redirect target is not allowed (SSRF protection).');
                    }
                },
            ],
            'headers' => [
                'User-Agent' => 'MediaDetectorBot/1.0 (compatible; URL Media Analyzer)',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ],
            'verify' => false,
        ]);
    }

    public function fetch(string $url): array
    {
        $resolvedUrl = null;

        try {
            $response = $this->client->get($url, [
                'on_stats' => function (TransferStats $stats) use (&$resolvedUrl) {
                    $resolvedUrl = (string) $stats->getEffectiveUri();
                },
            ]);

            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeaderLine('Content-Type');
            $body = (string) $response->getBody();

            // Check if it's directly a media file (not HTML). CDNs serve HLS/DASH
            // manifests with inconsistent content-types, so also match by URL.
            if ($this->isDirectMediaUrl($contentType) || $this->isManifestUrl($url)) {
                return [
                    'success' => true,
                    'type' => 'direct_media',
                    'url' => $url,
                    'resolved_url' => $resolvedUrl ?? $url,
                    'content_type' => $contentType,
                    'content_length' => $response->getHeaderLine('Content-Length') ?: null,
                    'content_disposition' => $response->getHeaderLine('Content-Disposition') ?: null,
                ];
            }

            // Check body size limit (10 MB)
            if (strlen($body) > 10 * 1024 * 1024) {
                return [
                    'success' => false,
                    'error_code' => 'FETCH_FAILED',
                    'error_message' => 'Page content exceeds maximum size limit.',
                ];
            }

            $needsJsRender = $this->needsJavaScriptRendering($body);

            return [
                'success' => true,
                'type' => 'html',
                'url' => $url,
                'resolved_url' => $resolvedUrl ?? $url,
                'status_code' => $statusCode,
                'content_type' => $contentType,
                'html' => $body,
                'needs_js_render' => $needsJsRender,
                'page_title' => $this->extractTitle($body),
            ];
        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;

            if ($statusCode === 401 || $statusCode === 403) {
                return [
                    'success' => false,
                    'error_code' => 'FETCH_FAILED',
                    'error_message' => 'This page requires authentication. Only publicly available assets are shown.',
                ];
            }

            if ($statusCode === 429) {
                return [
                    'success' => false,
                    'error_code' => 'RATE_LIMITED',
                    'error_message' => 'The target website is limiting our requests. Please try again later.',
                ];
            }

            Log::warning('PageCrawler fetch failed', [
                'url' => $url,
                'status' => $statusCode,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error_code' => $statusCode === null ? 'TIMEOUT' : 'FETCH_FAILED',
                'error_message' => $statusCode === null
                    ? 'The page took too long to load. Try again or try a simpler page.'
                    : 'We couldn\'t reach this URL. Please check that it\'s accessible.',
            ];
        } catch (\Exception $e) {
            Log::error('PageCrawler unexpected error', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error_code' => 'FETCH_FAILED',
                'error_message' => 'We couldn\'t reach this URL. Please check that it\'s accessible.',
            ];
        }
    }

    private function isDirectMediaUrl(string $contentType): bool
    {
        $mediaTypes = [
            'video/', 'audio/', 'application/pdf', 'application/zip',
            'application/x-rar', 'application/x-7z-compressed',
            'application/octet-stream', 'image/',
            'application/x-mpegurl', 'application/vnd.apple.mpegurl',
            'application/dash+xml', 'application/mpegurl',
        ];

        foreach ($mediaTypes as $type) {
            if (str_starts_with(strtolower($contentType), $type)) {
                return true;
            }
        }

        return false;
    }

    /** True when the URL path points at an HLS (.m3u8) or DASH (.mpd) manifest. */
    private function isManifestUrl(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return in_array($ext, ['m3u8', 'mpd'], true);
    }

    private function needsJavaScriptRendering(string $html): bool
    {
        // Check for SPA framework markers
        $spaSignals = [
            '<div id="app"></div>',
            '<div id="root"></div>',
            '<div id="__next">',
            '<div id="__nuxt">',
            'ng-app=',
            'data-reactroot',
        ];

        foreach ($spaSignals as $signal) {
            if (stripos($html, $signal) !== false) {
                return true;
            }
        }

        // Check for very minimal body content (likely JS-rendered)
        if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $html, $matches)) {
            $bodyContent = strip_tags($matches[1]);
            $bodyContent = trim(preg_replace('/\s+/', '', $bodyContent));
            if (strlen($bodyContent) < 50) {
                return true;
            }
        }

        return false;
    }

    private function extractTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $matches)) {
            return html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }
}
