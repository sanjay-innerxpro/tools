<?php

use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\ScanController;
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
