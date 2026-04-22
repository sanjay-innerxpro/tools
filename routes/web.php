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

Route::get('/sitemap.xml', function () {
    $today = now()->toDateString();

    $urls = collect(Route::getRoutes())
        ->filter(function ($route) {
            $uri = $route->uri();

            return in_array('GET', $route->methods(), true)
                && !str_contains($uri, '{')
                && !str_starts_with($uri, 'api/')
                && !str_starts_with($uri, '_')
                && $uri !== 'sitemap.xml';
        })
        ->map(function ($route) use ($today) {
            $uri = $route->uri();
            $path = $uri === '/' ? '/' : '/' . ltrim($uri, '/');

            $priority = '0.80';
            $changefreq = 'weekly';

            if ($path === '/') {
                $priority = '1.00';
                $changefreq = 'daily';
            } elseif ($path === '/tools/media-scanner') {
                $priority = '0.90';
            }

            return [
                'loc' => url($path),
                'lastmod' => $today,
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        })
        ->unique('loc')
        ->values();

    return response()
        ->view('sitemap', ['urls' => $urls], 200)
        ->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

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
Route::get('/tools/password-generator', fn() => view('tools.password-generator'));
Route::get('/tools/json-formatter', fn() => view('tools.json-formatter'));
Route::get('/tools/base64-encoder', fn() => view('tools.base64-encoder'));
Route::get('/tools/unit-converter', fn() => view('tools.unit-converter'));
Route::get('/tools/color-converter', fn() => view('tools.color-converter'));
Route::get('/tools/lorem-ipsum', fn() => view('tools.lorem-ipsum'));
Route::get('/tools/url-encoder', fn() => view('tools.url-encoder'));
Route::get('/tools/hash-generator', fn() => view('tools.hash-generator'));
Route::get('/tools/text-case-converter', fn() => view('tools.text-case-converter'));
Route::get('/tools/markdown-preview', fn() => view('tools.markdown-preview'));
Route::get('/tools/timestamp-converter', fn() => view('tools.timestamp-converter'));
Route::get('/tools/uuid-generator', fn() => view('tools.uuid-generator'));
Route::get('/tools/diff-checker', fn() => view('tools.diff-checker'));
Route::get('/tools/regex-tester', fn() => view('tools.regex-tester'));
Route::get('/tools/number-base-converter', fn() => view('tools.number-base-converter'));
Route::get('/tools/age-calculator', fn() => view('tools.age-calculator'));
Route::get('/tools/percentage-calculator', fn() => view('tools.percentage-calculator'));
Route::get('/tools/bmi-calculator', fn() => view('tools.bmi-calculator'));
Route::get('/tools/stopwatch', fn() => view('tools.stopwatch'));
Route::get('/tools/random-number-generator', fn() => view('tools.random-number-generator'));

// Download route for tool outputs
Route::get('/tools/download/{filename}', function (string $filename) {
    $filename = basename($filename); // prevent path traversal
    $path = storage_path('app/temp/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->download($path)->deleteFileAfterSend(true);
});
