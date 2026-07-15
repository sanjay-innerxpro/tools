<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;

class AssetEnricherService
{
    private Client $client;
    private const MAX_CONCURRENCY = 10;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'headers' => [
                'User-Agent' => 'MediaDetectorBot/1.0 (compatible; URL Media Analyzer)',
            ],
            'verify' => false,
        ]);
    }

    public function enrichAll(array $assets): array
    {
        $requests = [];
        $indexMap = [];

        foreach ($assets as $i => $asset) {
            if (!empty($asset['url']) && $asset['file_size'] === null) {
                $requests[] = new Request('HEAD', $asset['url']);
                $indexMap[] = $i;
            }
        }

        if (empty($requests)) {
            return $assets;
        }

        $pool = new Pool($this->client, $requests, [
            'concurrency' => self::MAX_CONCURRENCY,
            'fulfilled' => function ($response, $index) use (&$assets, $indexMap) {
                $assetIndex = $indexMap[$index];

                // File size
                $contentLength = $response->getHeaderLine('Content-Length');
                if ($contentLength && is_numeric($contentLength)) {
                    $assets[$assetIndex]['file_size'] = (int) $contentLength;
                }

                // MIME type
                $contentType = $response->getHeaderLine('Content-Type');
                if ($contentType && empty($assets[$assetIndex]['mime_type'])) {
                    $assets[$assetIndex]['mime_type'] = explode(';', $contentType)[0];
                }

                // Filename from Content-Disposition
                $disposition = $response->getHeaderLine('Content-Disposition');
                if ($disposition && preg_match('/filename[^;=\n]*=(["\']?)(.+?)\1(?:;|$)/i', $disposition, $m)) {
                    $assets[$assetIndex]['filename'] = $m[2];
                }
            },
            'rejected' => function ($reason, $index) use ($indexMap, &$assets) {
                $assetIndex = $indexMap[$index];
                Log::debug('HEAD request failed for asset', [
                    'url' => $assets[$assetIndex]['url'],
                    'reason' => $reason->getMessage(),
                ]);
            },
        ]);

        $pool->promise()->wait();

        return $assets;
    }

    public function parseHlsManifest(string $manifestUrl): array
    {
        try {
            $response = $this->client->get($manifestUrl, ['timeout' => 10]);
            $content = (string) $response->getBody();
            $variants = [];
            $lines = explode("\n", $content);

            for ($i = 0; $i < count($lines); $i++) {
                $line = trim($lines[$i]);

                if (str_starts_with($line, '#EXT-X-STREAM-INF:')) {
                    $attributes = $this->parseHlsAttributes($line);
                    $nextLine = isset($lines[$i + 1]) ? trim($lines[$i + 1]) : null;

                    if ($nextLine && !str_starts_with($nextLine, '#')) {
                        $streamUrl = $this->resolveStreamUrl($nextLine, $manifestUrl);
                        $bandwidth = $attributes['BANDWIDTH'] ?? 0;
                        $resolution = $attributes['RESOLUTION'] ?? '';

                        $variants[] = [
                            'label' => $resolution ?: ($bandwidth . 'bps'),
                            'url' => $streamUrl,
                            'bandwidth' => (int) $bandwidth,
                            'resolution' => $resolution,
                        ];
                    }
                }
            }

            // Sort by bandwidth descending
            usort($variants, fn($a, $b) => $b['bandwidth'] - $a['bandwidth']);

            return $variants;
        } catch (\Exception $e) {
            Log::debug('Failed to parse HLS manifest', ['url' => $manifestUrl, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function parseHlsAttributes(string $line): array
    {
        $attrs = [];
        $line = preg_replace('/^#EXT-X-STREAM-INF:/', '', $line);

        // Simple key=value parser
        preg_match_all('/([A-Z\-]+)=(?:"([^"]*)"|([^,]*))/', $line, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $attrs[$match[1]] = $match[2] !== '' ? $match[2] : $match[3];
        }

        return $attrs;
    }

    private function resolveStreamUrl(string $streamPath, string $manifestUrl): string
    {
        if (preg_match('/^https?:\/\//i', $streamPath)) {
            return $streamPath;
        }

        $baseDir = preg_replace('/[^\/]*$/', '', $manifestUrl);
        return $baseDir . $streamPath;
    }
}
