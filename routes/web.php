<?php

use App\Http\Controllers\Tools\PdfToTextController;
use App\Http\Controllers\Tools\ImageConverterController;
use App\Http\Controllers\Tools\MergePdfController;
use App\Http\Controllers\Tools\CompressImageController;
use App\Http\Controllers\Tools\SplitPdfController;
use App\Http\Controllers\Tools\ImageResizerController;
use App\Http\Controllers\Tools\VideoConverterController;
use App\Http\Controllers\Tools\AudioConverterController;
use Illuminate\Support\Facades\Route;

// Language switcher
Route::get('/set-locale/{locale}', function (string $locale) {
    $supported = ['en','hi','es','fr','zh','ar','pt','de','ja','ru'];
    if (!in_array($locale, $supported, true)) {
        $locale = 'en';
    }
    return redirect()->back()->withCookie(cookie()->forever('locale', $locale));
})->name('set-locale');

Route::get('/', function () {
    return view('home');
});

Route::get('/tools/media-scanner', function () {
    return view('scanner');
});

// Tool pages
Route::get('/tools/pdf-to-text', [PdfToTextController::class, 'index']);
Route::get('/tools/image-converter', [ImageConverterController::class, 'index']);
Route::get('/tools/merge-pdf', [MergePdfController::class, 'index']);
Route::get('/tools/compress-image', [CompressImageController::class, 'index']);
Route::get('/tools/split-pdf', [SplitPdfController::class, 'index']);
Route::get('/tools/image-resizer', [ImageResizerController::class, 'index']);
Route::get('/tools/video-converter', [VideoConverterController::class, 'index']);
Route::get('/tools/audio-converter', [AudioConverterController::class, 'index']);

// Client-side only tools (no backend needed)
Route::get('/tools/qr-code-generator', fn() => view('tools.qr-code-generator'));
Route::get('/tools/word-counter', fn() => view('tools.word-counter'));

// Download route for tool outputs
Route::get('/tools/download/{filename}', function (string $filename) {
    $filename = basename($filename); // prevent path traversal
    $path = storage_path('app/temp/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->download($path)->deleteFileAfterSend(true);
});
