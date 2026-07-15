<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessScanJob;
use App\Models\Scan;
use App\Services\UrlValidatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScanController extends Controller
{
    public function store(Request $request, UrlValidatorService $validator): JsonResponse
    {
        $request->validate([
            'url' => 'required|string|max:2048',
            'options' => 'nullable|array',
            'options.includeImages' => 'nullable|boolean',
            'options.minFileSize' => 'nullable|integer|min:0',
            'options.maxResults' => 'nullable|integer|min:1|max:500',
        ]);

        $url = $request->input('url');
        $options = $request->input('options', []);

        // Validate URL (SSRF protection)
        $validation = $validator->validate($url);
        if (!$validation['valid']) {
            return response()->json([
                'error' => [
                    'code' => $validation['error_code'],
                    'message' => $validation['error_message'],
                ],
            ], 422);
        }

        // Check rate limit for concurrent scans.
        // Only count genuinely active scans: a real scan finishes in 5-15s, so any
        // queued/processing record older than a few minutes is a crashed/stuck job
        // and must not lock the user out. Mark those as failed before counting.
        $ip = $request->ip();
        Scan::where('ip_address', $ip)
            ->whereIn('status', ['queued', 'processing'])
            ->where('created_at', '<', now()->subMinutes(5))
            ->update([
                'status' => 'failed',
                'error_code' => 'SCAN_TIMEOUT',
                'error_message' => 'Scan did not complete in time.',
            ]);

        $concurrentCount = Scan::where('ip_address', $ip)
            ->whereIn('status', ['queued', 'processing'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($concurrentCount >= 2) {
            return response()->json([
                'error' => [
                    'code' => 'QUOTA_EXCEEDED',
                    'message' => 'You already have 2 scans in progress. Please wait for them to complete.',
                ],
            ], 429);
        }

        // Check cached results — only reuse if the scan actually found real media assets
        $cacheKey = 'scan_result:' . md5($url . json_encode($options));
        $cachedScanId = Cache::get($cacheKey);
        if ($cachedScanId) {
            $cachedScan = Scan::find($cachedScanId);
            if ($cachedScan && $cachedScan->status === 'completed' && $cachedScan->assets()->count() > 0) {
                return response()->json([
                    'scanId' => $cachedScan->id,
                    'status' => 'completed',
                    'cached' => true,
                    'resultsUrl' => "/api/scan/{$cachedScan->id}/results",
                ], 200);
            }
            // Stale or empty — delete old record and re-scan
            if ($cachedScan) {
                $cachedScan->assets()->delete();
                $cachedScan->delete();
            }
            Cache::forget($cacheKey);
        }

        // Create scan
        $scan = Scan::create([
            'url' => $validation['url'],
            'status' => 'queued',
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'options' => $options,
            'expires_at' => now()->addHour(),
        ]);

        // Cache for 1 hour
        Cache::put($cacheKey, $scan->id, 3600);

        // Dispatch job
        ProcessScanJob::dispatch($scan->id);

        return response()->json([
            'scanId' => $scan->id,
            'status' => 'queued',
            'estimatedDuration' => '5-15s',
            'statusUrl' => "/api/scan/{$scan->id}/status",
        ], 202);
    }

    public function status(string $scanId): StreamedResponse|JsonResponse
    {
        $scan = Scan::find($scanId);
        if (!$scan) {
            return response()->json([
                'error' => [
                    'code' => 'SCAN_NOT_FOUND',
                    'message' => 'Scan not found or has expired.',
                ],
            ], 404);
        }

        return response()->stream(function () use ($scan) {
            $lastPhase = null;

            for ($i = 0; $i < 60; $i++) {
                if (connection_aborted()) break;

                $progress = Cache::get("scan_progress:{$scan->id}");
                $scan->refresh();

                if ($progress && ($progress['phase'] ?? '') !== $lastPhase) {
                    $lastPhase = $progress['phase'];

                    echo "event: progress\n";
                    echo "data: " . json_encode($progress) . "\n\n";
                    ob_flush();
                    flush();

                    if (in_array($progress['phase'], ['done', 'failed'])) {
                        if ($progress['phase'] === 'done') {
                            echo "event: complete\n";
                            echo "data: " . json_encode([
                                'phase' => 'done',
                                'totalAssets' => $scan->asset_count,
                                'resultsUrl' => "/api/scan/{$scan->id}/results",
                            ]) . "\n\n";
                        } else {
                            echo "event: error\n";
                            echo "data: " . json_encode([
                                'code' => $scan->error_code,
                                'message' => $scan->error_message,
                            ]) . "\n\n";
                        }
                        ob_flush();
                        flush();
                        break;
                    }
                }

                // Also check DB status directly
                if ($scan->status === 'completed') {
                    echo "event: complete\n";
                    echo "data: " . json_encode([
                        'phase' => 'done',
                        'totalAssets' => $scan->asset_count,
                        'resultsUrl' => "/api/scan/{$scan->id}/results",
                    ]) . "\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                if ($scan->status === 'failed') {
                    echo "event: error\n";
                    echo "data: " . json_encode([
                        'code' => $scan->error_code,
                        'message' => $scan->error_message,
                    ]) . "\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function results(string $scanId): JsonResponse
    {
        $scan = Scan::with('assets')->find($scanId);

        if (!$scan) {
            return response()->json([
                'error' => [
                    'code' => 'SCAN_NOT_FOUND',
                    'message' => 'Scan not found or has expired.',
                ],
            ], 404);
        }

        if ($scan->status === 'queued' || $scan->status === 'processing') {
            return response()->json([
                'scanId' => $scan->id,
                'status' => $scan->status,
                'message' => 'Scan is still in progress.',
            ], 202);
        }

        if ($scan->status === 'failed') {
            return response()->json([
                'scanId' => $scan->id,
                'status' => 'failed',
                'error' => [
                    'code' => $scan->error_code,
                    'message' => $scan->error_message,
                ],
            ], 200);
        }

        $byType = $scan->assets->groupBy('type')->map->count();

        $stats = [
            'totalAssets' => $scan->assets->count(),
            'byType' => $byType->isEmpty() ? new \stdClass() : $byType,
            'totalSize' => $scan->assets->sum('file_size'),
            'drmProtected' => $scan->assets->where('is_drm', true)->count(),
        ];

        $assets = $scan->assets->map(function ($asset) {
            return [
                'id' => $asset->id,
                'url' => $asset->url,
                'filename' => $asset->filename,
                'type' => $asset->type,
                'mimeType' => $asset->mime_type,
                'extension' => $asset->extension,
                'size' => $asset->file_size,
                'sizeFormatted' => $asset->size_formatted,
                'quality' => $asset->quality,
                'qualityVariants' => $asset->quality_variants,
                'thumbnailUrl' => $asset->thumbnail_url,
                'drm' => $asset->is_drm,
                'downloadable' => $asset->is_downloadable,
                'source' => $asset->source,
                'downloadUrl' => $asset->is_downloadable ? "/api/download/{$asset->id}" : null,
                'hlsDownloadUrl' => ($asset->is_downloadable && in_array($asset->extension, ['m3u8', 'mpd']))
                    ? "/api/download-hls/{$asset->id}/start"
                    : null,
            ];
        });

        $needsJsRender = ($scan->options['needs_js_render'] ?? false);

        return response()->json([
            'scanId' => $scan->id,
            'url' => $scan->url,
            'resolvedUrl' => $scan->resolved_url,
            'pageTitle' => $scan->page_title,
            'scannedAt' => $scan->created_at->toIso8601String(),
            'stats' => $stats,
            'assets' => $assets,
            'warnings' => $needsJsRender && $scan->assets->isEmpty()
                ? ['This page uses JavaScript to load content dynamically. Some media may not be detectable without a headless browser.']
                : [],
        ]);
    }

    public function destroy(string $scanId): JsonResponse
    {
        $scan = Scan::find($scanId);

        if (!$scan) {
            return response()->json([
                'error' => [
                    'code' => 'SCAN_NOT_FOUND',
                    'message' => 'Scan not found.',
                ],
            ], 404);
        }

        $scan->assets()->delete();
        $scan->delete();

        return response()->json(['message' => 'Scan deleted.']);
    }

    public function poll(string $scanId): JsonResponse
    {
        $scan = Scan::find($scanId);

        if (!$scan) {
            return response()->json([
                'error' => [
                    'code' => 'SCAN_NOT_FOUND',
                    'message' => 'Scan not found.',
                ],
            ], 404);
        }

        $progress = Cache::get("scan_progress:{$scan->id}", [
            'phase' => $scan->status,
            'progress' => $scan->status === 'completed' ? 100 : ($scan->status === 'failed' ? 100 : 0),
            'message' => match($scan->status) {
                'queued' => 'Waiting in queue...',
                'processing' => 'Processing...',
                'completed' => 'Scan complete.',
                'failed' => $scan->error_message ?? 'Scan failed.',
            },
        ]);

        return response()->json([
            'scanId' => $scan->id,
            'status' => $scan->status,
            'progress' => $progress,
        ]);
    }
}
