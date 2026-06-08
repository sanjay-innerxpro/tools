<?php
// TEMPORARY DIAGNOSTIC — delete this file after running.
// Visit: https://tools.smartsoch.store/diag.php?key=check123
// Tells you what this shared host actually allows.

if (($_GET['key'] ?? '') !== 'check123') {
    http_response_code(403);
    exit('forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

function has_func($f) {
    $disabled = array_map('trim', explode(',', strtolower((string) ini_get('disabled_functions'))));
    if (in_array(strtolower($f), $disabled, true)) return "DISABLED (in disabled_functions)";
    return function_exists($f) ? "available" : "missing";
}

function which($bin) {
    if (!function_exists('shell_exec')) return "can't check (shell_exec unavailable)";
    $out = @shell_exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null');
    return $out ? trim($out) : "NOT FOUND on PATH";
}

echo "=== PHP ===\n";
echo "version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . " (" . PHP_OS_FAMILY . ")\n";
echo "disabled_functions: " . (ini_get('disabled_functions') ?: "(none)") . "\n\n";

echo "=== Shell functions ===\n";
foreach (['exec', 'shell_exec', 'popen', 'proc_open', 'system', 'passthru'] as $f) {
    echo str_pad($f, 12) . ": " . has_func($f) . "\n";
}

echo "\n=== Binaries on PATH ===\n";
foreach (['python3', 'python', 'pip3', 'ffmpeg', 'yt-dlp'] as $bin) {
    echo str_pad($bin, 10) . ": " . which($bin) . "\n";
}

echo "\n=== Python version ===\n";
if (function_exists('shell_exec')) {
    echo trim((string) @shell_exec('python3 --version 2>&1')) ?: "(no output)";
} else {
    echo "shell_exec unavailable";
}
echo "\n\n=== PHP image fallback (no Python needed) ===\n";
echo "GD extension : " . (extension_loaded('gd') ? "loaded" : "missing") . "\n";
echo "Imagick ext  : " . (extension_loaded('imagick') ? "loaded" : "missing") . "\n";

echo "\nDONE. Delete this file when finished.\n";
