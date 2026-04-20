<?php

use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Tools\PdfToTextController;
use App\Http\Controllers\Tools\ImageConverterController;
use App\Http\Controllers\Tools\MergePdfController;
use App\Http\Controllers\Tools\CompressImageController;
use App\Http\Controllers\Tools\SplitPdfController;
use App\Http\Controllers\Tools\ImageResizerController;
use App\Http\Controllers\Tools\VideoConverterController;
use App\Http\Controllers\Tools\AudioConverterController;
use Illuminate\Support\Facades\Route;

// Scan creation — strict rate limit
Route::post('/scan', [ScanController::class, 'store'])
    ->middleware('throttle:scan');

// Read endpoints — generous limit
Route::get('/scan/{scanId}/status', [ScanController::class, 'status']);
Route::get('/scan/{scanId}/poll', [ScanController::class, 'poll']);
Route::get('/scan/{scanId}/results', [ScanController::class, 'results']);
Route::delete('/scan/{scanId}', [ScanController::class, 'destroy']);

Route::get('/download/{assetId}', [DownloadController::class, 'download'])
    ->middleware('throttle:download');

Route::post('/download-hls/{assetId}/start', [DownloadController::class, 'startHlsDownload']);
Route::get('/download-hls/status/{taskId}', [DownloadController::class, 'hlsDownloadStatus']);
Route::get('/download-hls/file/{taskId}', [DownloadController::class, 'hlsDownloadFile']);

// Tool API endpoints
Route::post('/tools/pdf-to-text', [PdfToTextController::class, 'convert']);
Route::post('/tools/image-convert', [ImageConverterController::class, 'convert']);
Route::post('/tools/merge-pdf', [MergePdfController::class, 'merge']);
Route::post('/tools/compress-image', [CompressImageController::class, 'compress']);
Route::post('/tools/split-pdf', [SplitPdfController::class, 'split']);
Route::post('/tools/image-resize', [ImageResizerController::class, 'resize']);
Route::post('/tools/video-convert', [VideoConverterController::class, 'convert']);
Route::post('/tools/audio-convert', [AudioConverterController::class, 'convert']);
