<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScanAsset;
use App\Services\YtDlpService;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    private const MAX_PROXY_SIZE = 2 * 1024 * 1024 * 1024; // 2 GB

    public function download(string $assetId): StreamedResponse|JsonResponse
    {
        $asset = ScanAsset::find($assetId);

        if (!$asset) {
            return response()->json([
                'error' => [
                    'code' => 'ASSET_NOT_FOUND',
                    'message' => 'Asset not found in scan results.',
                ],
            ], 404);
        }

        if ($asset->is_drm) {
            return response()->json([
                'error' => [
                    'code' => 'DRM_PROTECTED',
                    'message' => 'This content is DRM-protected and cannot be downloaded.',
                ],
            ], 403);
        }

        if (!$asset->is_downloadable) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_DOWNLOADABLE',
                    'message' => 'This asset cannot be downloaded.',
                ],
            ], 403);
        }

        // For large files, return the direct URL
        if ($asset->file_size && $asset->file_size > self::MAX_PROXY_SIZE) {
            return response()->json([
                'directUrl' => $asset->url,
                'message' => 'File is too large to proxy. Use the direct URL.',
            ]);
        }

        $filename = $asset->filename ?? 'download';
        // Sanitize filename for Content-Disposition header
        $filename = preg_replace('/[^\w\.\-]/', '_', $filename);

        return response()->stream(function () use ($asset) {
            $client = new Client([
                'timeout' => 300,
                'stream' => true,
                'headers' => [
                    'User-Agent' => 'MediaDetectorBot/1.0',
                ],
                'verify' => false,
            ]);

            try {
                $response = $client->get($asset->url, ['stream' => true]);
                $body = $response->getBody();

                while (!$body->eof()) {
                    echo $body->read(8192);
                    flush();
                }

                $body->close();
            } catch (\Exception $e) {
                // Connection may already be closed
            }
        }, 200, [
            'Content-Type' => $asset->mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => $asset->file_size ?? null,
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Start an HLS-to-MP4 download in background. Returns task ID immediately.
     */
    public function startHlsDownload(string $assetId, YtDlpService $ytDlp): JsonResponse
    {
        $asset = ScanAsset::find($assetId);

        if (!$asset) {
            return response()->json(['error' => ['code' => 'ASSET_NOT_FOUND', 'message' => 'Asset not found.']], 404);
        }

        $ext = strtolower($asset->extension ?? '');
        if (!in_array($ext, ['m3u8', 'mpd']) && !str_contains($asset->mime_type ?? '', 'mpegURL') && !str_contains($asset->mime_type ?? '', 'dash+xml')) {
            return response()->json(['error' => ['code' => 'NOT_STREAM', 'message' => 'Asset is not an HLS/DASH stream.']], 400);
        }

        $taskId = uniqid('dl_', true);
        $taskId = preg_replace('/[^a-zA-Z0-9_]/', '_', $taskId);

        $ytDlp->startBackgroundDownload($asset->url, $taskId);

        return response()->json([
            'taskId' => $taskId,
            'message' => 'Download started',
        ]);
    }

    /**
     * Check progress of a background HLS download.
     */
    public function hlsDownloadStatus(string $taskId, YtDlpService $ytDlp): JsonResponse
    {
        $taskId = preg_replace('/[^a-zA-Z0-9_]/', '', $taskId);

        $progress = $ytDlp->checkDownloadProgress($taskId);

        return response()->json($progress);
    }

    /**
     * Download the completed MP4 file for a task.
     */
    public function hlsDownloadFile(string $taskId, YtDlpService $ytDlp): StreamedResponse|JsonResponse
    {
        $taskId = preg_replace('/[^a-zA-Z0-9_]/', '', $taskId);
        $filePath = $ytDlp->getDownloadPath($taskId);

        if (!$filePath) {
            return response()->json(['error' => ['code' => 'NOT_READY', 'message' => 'File not ready yet.']], 404);
        }

        $fileSize = filesize($filePath);

        return response()->stream(function () use ($filePath, $ytDlp, $taskId) {
            $handle = fopen($filePath, 'rb');
            while (!feof($handle)) {
                echo fread($handle, 65536);
                flush();
            }
            fclose($handle);
            // Defer cleanup slightly to ensure browser has fully received the data
            register_shutdown_function(function () use ($ytDlp, $taskId) {
                $ytDlp->cleanupTask($taskId);
            });
        }, 200, [
            'Content-Type' => 'video/mp4',
            'Content-Disposition' => 'attachment; filename="video.mp4"',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'no-cache',
        ]);
    }
}
