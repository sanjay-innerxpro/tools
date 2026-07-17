<?php

use App\Http\Controllers\Tools\PdfToTextController;
use App\Http\Controllers\Tools\ImageConverterController;
use App\Http\Controllers\Tools\MergePdfController;
use App\Http\Controllers\Tools\CompressImageController;
use App\Http\Controllers\Tools\SplitPdfController;
use App\Http\Controllers\Tools\ImageResizerController;
use App\Http\Controllers\Tools\VideoConverterController;
use App\Http\Controllers\Tools\AudioConverterController;
use App\Support\SeoMeta;
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

Route::get('/seo/preview', function () {
    abort_unless(config('app.debug'), 404);

    $appName = config('app.name', 'ToolBox');
    $defaultDescription = __('Use :app free online tools to scan media URLs, convert files, and handle daily tasks instantly. No signup required.', ['app' => $appName]);

    $toolDescriptions = [
        'tools/media-scanner' => __('Scan any public URL to detect downloadable videos, audio, and media assets. Fast browser-based media discovery with no signup required.'),
        'tools/pdf-to-text' => __('Extract clean text from PDF files in seconds using this free and simple PDF to text converter.'),
        'tools/image-converter' => __('Convert images between JPG, PNG, WebP, GIF, and more formats with a fast free image converter.'),
        'tools/merge-pdf' => __('Merge multiple PDF files into one organized document quickly with a free online PDF combiner.'),
        'tools/compress-image' => __('Reduce image file size without major quality loss using this quick online image compressor.'),
        'tools/split-pdf' => __('Split PDF files into separate pages or custom page ranges quickly and securely online.'),
        'tools/image-resizer' => __('Resize images to exact width and height or percentage while keeping quality and proportions.'),
        'tools/video-converter' => __('Convert video files between popular formats like MP4, WebM, AVI, and MOV quickly in your browser.'),
        'tools/audio-converter' => __('Convert audio between MP3, WAV, AAC, FLAC, and other formats quickly with this free tool.'),
        'tools/qr-code-generator' => __('Create QR codes online for URLs, text, and contact details with quick download options.'),
        'tools/word-counter' => __('Count words, characters, sentences, and reading time instantly with this free online word and character counter.'),
        'tools/password-generator' => __('Generate strong random passwords with custom length and character options for better account security.'),
        'tools/otp-generator' => __('Generate secure one-time passwords instantly and copy them with one click.'),
        'tools/json-formatter' => __('Format, validate, and beautify JSON data online to improve readability and catch syntax issues.'),
        'tools/base64-encoder' => __('Encode text to Base64 or decode Base64 strings instantly for APIs, tokens, and debugging.'),
        'tools/unit-converter' => __('Convert length, weight, temperature, and other units accurately with this fast free unit converter.'),
        'tools/color-converter' => __('Convert color values between HEX, RGB, HSL, and more formats for design and frontend work.'),
        'tools/lorem-ipsum' => __('Generate Lorem Ipsum placeholder text by paragraph, sentence, or word for design and content mockups.'),
        'tools/url-encoder' => __('Encode and decode URL strings safely for query parameters, redirects, and web development tasks.'),
        'tools/hash-generator' => __('Create MD5, SHA-1, SHA-256, and other hashes instantly for verification and development tasks.'),
        'tools/text-case-converter' => __('Change text to uppercase, lowercase, title case, or sentence case instantly with one click.'),
        'tools/markdown-preview' => __('Write and preview Markdown in real time with formatted output for docs, notes, and README files.'),
        'tools/timestamp-converter' => __('Convert Unix timestamps to readable dates and transform dates back to timestamps in seconds.'),
        'tools/uuid-generator' => __('Generate secure UUIDs instantly for apps, APIs, databases, and development workflows.'),
        'tools/diff-checker' => __('Compare two text blocks and highlight differences instantly to review edits and spot changes.'),
        'tools/regex-tester' => __('Test regular expressions with live matches, flags, and instant feedback for faster debugging.'),
        'tools/number-base-converter' => __('Convert numbers between binary, octal, decimal, and hexadecimal formats instantly.'),
        'tools/age-calculator' => __('Calculate your exact age from your date of birth'),
        'tools/percentage-calculator' => __('Quick percentage calculations for everyday use'),
        'tools/bmi-calculator' => __('Calculate your Body Mass Index instantly'),
        'tools/stopwatch' => __('Precise stopwatch with lap time tracking'),
        'tools/random-number-generator' => __('Generate random numbers in any range'),
    ];

    $rows = collect(Route::getRoutes())
        ->filter(function ($route) {
            $uri = $route->uri();

            return in_array('GET', $route->methods(), true)
                && !str_contains($uri, '{')
                && !str_starts_with($uri, 'api/')
                && !str_starts_with($uri, '_')
                && !in_array($uri, ['sitemap.xml', 'seo/preview'], true);
        })
        ->map(function ($route) use ($appName, $defaultDescription, $toolDescriptions) {
            $uri = $route->uri();
            $path = $uri === '/' ? '/' : '/' . ltrim($uri, '/');
            $key = ltrim($path, '/');

            $title = $appName . ' — ' . __('Free Online Media & File Tools');
            if ($key === 'tools/media-scanner') {
                $title = $appName . ' — ' . __('URL Media Scanner');
            } elseif (str_starts_with($key, 'tools/')) {
                $slug = str_replace('tools/', '', $key);
                $title = $appName . ' — ' . str_replace('-', ' ', ucwords($slug, '-'));
            }

            $description = $toolDescriptions[$key] ?? ($key === '' ? __('Free online media and file tools: scan media URLs, convert PDFs and images, optimize files, and use fast utility tools with no signup required.') : $defaultDescription);
            $canonical = url($path);
            $image = SeoMeta::defaultImageForPath($key);

            return [
                'path' => $path,
                'title' => $title,
                'description' => $description,
                'canonical' => $canonical,
                'og_image' => $image,
                'twitter_image' => $image,
            ];
        })
        ->sortBy('path')
        ->values();

    return view('seo-preview', ['rows' => $rows]);
})->name('seo.preview');

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
Route::get('/tools/otp-generator', fn() => view('tools.otp-generator'));
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
