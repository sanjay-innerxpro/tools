<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$url = 'https://tldv.io/app/meetings/69cdf8ac507ec900137bd498';
echo "Testing: {$url}\n\n";

// === Phase 1: Static fetch ===
echo "=== PHASE 1: Static HTTP ===\n";
$crawler = app(\App\Services\PageCrawlerService::class);
$r1 = $crawler->fetch($url);
echo "Success: " . ($r1['success'] ? 'yes' : 'no') . "\n";
echo "Type: " . ($r1['type'] ?? 'N/A') . "\n";
echo "Status: " . ($r1['status_code'] ?? 'N/A') . "\n";
echo "Resolved URL: " . ($r1['resolved_url'] ?? 'N/A') . "\n";
echo "Needs JS: " . (($r1['needs_js_render'] ?? false) ? 'yes' : 'no') . "\n";
echo "HTML length: " . strlen($r1['html'] ?? '') . "\n";
if (!empty($r1['error_message'])) echo "Error: " . $r1['error_message'] . "\n";

// Check for auth signals in HTML
if (!empty($r1['html'])) {
    $h = $r1['html'];
    echo "Has 'login': " . (stripos($h, 'login') !== false ? 'yes' : 'no') . "\n";
    echo "Has 'sign in': " . (stripos($h, 'sign in') !== false ? 'yes' : 'no') . "\n";
    echo "Has 'auth': " . (stripos($h, 'auth') !== false ? 'yes' : 'no') . "\n";
    echo "Has 'meeting': " . (stripos($h, 'meeting') !== false ? 'yes' : 'no') . "\n";
}

// === Phase 2: Headless browser ===
echo "\n=== PHASE 2: Headless Browser ===\n";
$hb = app(\App\Services\HeadlessBrowserService::class);
$r2 = $hb->render($url);
echo "Success: " . ($r2['success'] ? 'yes' : 'no') . "\n";
echo "HTML length: " . strlen($r2['html'] ?? '') . "\n";
echo "Intercepted URLs: " . count($r2['intercepted_urls'] ?? []) . "\n";
if (!empty($r2['error'])) echo "Error: " . $r2['error'] . "\n";

if (!empty($r2['intercepted_urls'])) {
    echo "\nIntercepted:\n";
    foreach ($r2['intercepted_urls'] as $u) {
        echo "  - " . substr($u['url'], 0, 120) . "\n";
        echo "    CT: " . ($u['content_type'] ?? 'unknown') . "\n";
    }
}

if (!empty($r2['html'])) {
    $h = $r2['html'];
    echo "\nHTML signals:\n";
    echo "  Has 'login': " . (stripos($h, 'login') !== false ? 'yes' : 'no') . "\n";
    echo "  Has 'sign in': " . (stripos($h, 'sign in') !== false ? 'yes' : 'no') . "\n";
    echo "  Has 'video': " . (stripos($h, 'video') !== false ? 'yes' : 'no') . "\n";
    echo "  Has 'm3u8': " . (stripos($h, 'm3u8') !== false ? 'yes' : 'no') . "\n";
    echo "  Has 'mp4': " . (stripos($h, '.mp4') !== false ? 'yes' : 'no') . "\n";
    echo "  Has 'meet': " . (stripos($h, 'meeting') !== false ? 'yes' : 'no') . "\n";
    echo "  Current URL in page: " . "\n";

    // Try to extract what the page title is
    if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $h, $m)) {
        echo "  Title: " . strip_tags($m[1]) . "\n";
    }

    // Snapshot of body text
    $bodyText = strip_tags($h);
    $bodyText = preg_replace('/\s+/', ' ', $bodyText);
    echo "  Body snippet (first 500 chars): " . substr(trim($bodyText), 0, 500) . "\n";

    // Look for any API calls or data in scripts
    preg_match_all('/"(https?:\/\/[^"]{10,})"/', $h, $urlMatches);
    $foundUrls = array_unique($urlMatches[1]);
    $mediaUrls = array_filter($foundUrls, fn($u) => preg_match('/\.(mp4|m3u8|mpd|webm|mov)(\?|$)/i', $u));
    if ($mediaUrls) {
        echo "\n  Media URLs found in rendered HTML:\n";
        foreach ($mediaUrls as $mu) {
            echo "    - " . substr($mu, 0, 120) . "\n";
        }
    }
}

// === Phase 3: yt-dlp ===
echo "\n=== PHASE 3: yt-dlp ===\n";
$yt = app(\App\Services\YtDlpService::class);
$r3 = $yt->extract($url);
echo "Success: " . ($r3['success'] ? 'yes' : 'no') . "\n";
echo "Title: " . ($r3['title'] ?? 'N/A') . "\n";
echo "Assets: " . count($r3['assets'] ?? []) . "\n";
if (!empty($r3['error'])) echo "Error: " . $r3['error'] . "\n";
