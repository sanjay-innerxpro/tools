<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class YtDlpService
{
    private const PYTHON_EXECUTABLE = 'python';

    public function extract(string $url): array
    {
        try {
            $result = $this->runYtDlp($url);
            return $result;
        } catch (\Exception $e) {
            Log::warning('YtDlp extraction failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'assets' => [],
            ];
        }
    }

    private function runYtDlp(string $url): array
    {
        $outputFile = storage_path('app/ytdlp_out_' . uniqid() . '.json');

        $script = $this->buildScript($url, $outputFile);
        $scriptPath = storage_path('app/ytdlp_' . uniqid() . '.py');

        $dir = dirname($scriptPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($scriptPath, $script);

        try {
            $python = env('PYTHON_EXECUTABLE', self::PYTHON_EXECUTABLE);
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = sprintf('cmd /c ""%s" "%s" 2>nul"', $python, $scriptPath);
            } else {
                $cmd = sprintf('"%s" "%s" 2>/dev/null', $python, $scriptPath);
            }

            $returnCode = 0;
            \exec($cmd, $outputLines, $returnCode);

            if (!file_exists($outputFile)) {
                throw new \RuntimeException('yt-dlp script produced no output (rc=' . $returnCode . ')');
            }

            $raw = file_get_contents($outputFile);
            $data = json_decode($raw, true);

            if (!is_array($data)) {
                throw new \RuntimeException('Invalid JSON from yt-dlp script: ' . substr($raw, 0, 300));
            }

            if (!empty($data['error'])) {
                return [
                    'success' => false,
                    'error' => $data['error'],
                    'assets' => [],
                ];
            }

            $assets = $this->parseFormats($data);

            return [
                'success' => true,
                'assets' => $assets,
                'title' => $data['title'] ?? null,
                'thumbnail' => $data['thumbnail'] ?? null,
                'duration' => $data['duration'] ?? null,
            ];
        } finally {
            @unlink($scriptPath);
            @unlink($outputFile);
        }
    }

    private function buildScript(string $url, string $outputFile): string
    {
        $escapedUrl = addslashes($url);
        $escapedOutput = addslashes($outputFile);

        return <<<PYTHON
import sys, json, os

import yt_dlp

url = '{$escapedUrl}'
output_file = r'{$escapedOutput}'

ydl_opts = {
    'quiet': True,
    'no_warnings': True,
    'skip_download': True,
    'noplaylist': True,
    'extract_flat': False,
    'socket_timeout': 30,
    'http_headers': {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    },
    # Do not use cookiesfrombrowser to avoid privacy/permission issues
}

try:
    with yt_dlp.YoutubeDL(ydl_opts) as ydl:
        info = ydl.extract_info(url, download=False)
        # Remove large binary fields
        for key in ('thumbnails', 'automatic_captions', 'subtitles', 'heatmap', 'chapters'):
            info.pop(key, None)
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(info, f, ensure_ascii=False, default=str)
except yt_dlp.utils.DownloadError as e:
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump({'error': str(e)}, f)
except Exception as e:
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump({'error': str(e)}, f)
PYTHON;
    }

    private function parseFormats(array $info): array
    {
        $assets = [];
        $title = $info['title'] ?? 'media';
        $ext = $info['ext'] ?? 'mp4';
        $type = $this->classifyMediaType($info['ext'] ?? '', $info['acodec'] ?? '', $info['vcodec'] ?? '');

        // If there are multiple formats, expose them
        if (!empty($info['formats'])) {
            foreach ($info['formats'] as $fmt) {
                $fmtUrl = $fmt['url'] ?? null;
                if (empty($fmtUrl) || !filter_var($fmtUrl, FILTER_VALIDATE_URL)) continue;

                // Skip manifest containers that just list other formats
                $fmtExt = strtolower($fmt['ext'] ?? '');
                if (in_array($fmtExt, ['mhtml', 'none', ''])) continue;

                $fmtType = $this->classifyMediaType(
                    $fmt['ext'] ?? '',
                    $fmt['acodec'] ?? '',
                    $fmt['vcodec'] ?? ''
                );

                $quality = $this->buildQualityLabel($fmt);

                $assets[] = [
                    'url' => $fmtUrl,
                    'filename' => $this->sanitizeFilename($title) . '_' . ($fmt['format_id'] ?? 'format') . '.' . $fmtExt,
                    'type' => $fmtType ?? 'video',
                    'mime_type' => $fmt['mime_type'] ?? null,
                    'extension' => $fmtExt ?: null,
                    'file_size' => isset($fmt['filesize']) ? (int) $fmt['filesize'] : (isset($fmt['filesize_approx']) ? (int) $fmt['filesize_approx'] : null),
                    'quality' => $quality,
                    'quality_variants' => null,
                    'source' => 'yt-dlp',
                    'is_drm' => false,
                    'is_downloadable' => true,
                ];
            }
        }

        // If no formats but there's a direct URL in info
        if (empty($assets) && !empty($info['url'])) {
            $assets[] = [
                'url' => $info['url'],
                'filename' => $this->sanitizeFilename($title) . '.' . $ext,
                'type' => $type ?? 'video',
                'mime_type' => null,
                'extension' => $ext,
                'file_size' => $info['filesize'] ?? null,
                'quality' => null,
                'quality_variants' => null,
                'source' => 'yt-dlp',
                'is_drm' => false,
                'is_downloadable' => true,
            ];
        }

        // Deduplicate by URL
        $seen = [];
        $result = [];
        foreach ($assets as $asset) {
            if (!isset($seen[$asset['url']])) {
                $seen[$asset['url']] = true;
                $result[] = $asset;
            }
        }

        return $result;
    }

    private function buildQualityLabel(array $fmt): ?string
    {
        $parts = [];

        if (!empty($fmt['height'])) {
            $parts[] = $fmt['height'] . 'p';
        }
        if (!empty($fmt['fps']) && $fmt['fps'] > 30) {
            $parts[] = (int) $fmt['fps'] . 'fps';
        }
        if (!empty($fmt['format_note'])) {
            $parts[] = $fmt['format_note'];
        }

        return $parts ? implode(' ', $parts) : ($fmt['format_id'] ?? null);
    }

    private function classifyMediaType(string $ext, string $acodec, string $vcodec): string
    {
        $videoExts = ['mp4', 'webm', 'flv', 'mkv', 'avi', 'mov', 'ogv', 'm3u8', 'mpd', 'ts'];
        $audioExts = ['mp3', 'aac', 'm4a', 'ogg', 'oga', 'opus', 'flac', 'wav', 'wma'];

        $ext = strtolower($ext);
        if (in_array($ext, $videoExts)) return 'video';
        if (in_array($ext, $audioExts)) return 'audio';

        // Infer from codec info
        $hasVideo = !empty($vcodec) && $vcodec !== 'none';
        $hasAudio = !empty($acodec) && $acodec !== 'none';

        if ($hasVideo) return 'video';
        if ($hasAudio) return 'audio';

        return 'video';
    }

    /**
     * Start a background download process. Returns the task ID immediately.
     */
    public function startBackgroundDownload(string $url, string $taskId): void
    {
        $outputPath = storage_path("app/hls_{$taskId}.mp4");
        $script = $this->buildDownloadScript($url, $outputPath);
        $scriptPath = storage_path("app/hls_{$taskId}.py");

        file_put_contents($scriptPath, $script);

        $python = env('PYTHON_EXECUTABLE', self::PYTHON_EXECUTABLE);
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = sprintf('start /B cmd /c ""%s" "%s" > nul 2>&1"', $python, $scriptPath);
        } else {
            $cmd = sprintf('nohup "%s" "%s" > /dev/null 2>&1 &', $python, $scriptPath);
        }
        \pclose(\popen($cmd, 'r'));
    }

    /**
     * Check the progress of a background download task.
     */
    public function checkDownloadProgress(string $taskId): array
    {
        $progressFile = storage_path("app/hls_{$taskId}.progress");
        $outputPath = storage_path("app/hls_{$taskId}.mp4");

        // Check progress file FIRST to avoid race condition during ffmpeg write
        if (file_exists($progressFile)) {
            clearstatcache(true, $progressFile);
            $data = json_decode(file_get_contents($progressFile), true);
            if (is_array($data)) {
                // If Python wrote 'done', verify the file actually exists
                if (($data['status'] ?? '') === 'done' && file_exists($outputPath)) {
                    $data['fileSize'] = filesize($outputPath);
                }
                return $data;
            }
        }

        // Fallback: no progress file but MP4 exists (progress file already cleaned up)
        if (file_exists($outputPath) && filesize($outputPath) > 1024) {
            return [
                'status' => 'done',
                'fileSize' => filesize($outputPath),
            ];
        }

        return ['status' => 'starting'];
    }

    /**
     * Get the file path for a completed download.
     */
    public function getDownloadPath(string $taskId): ?string
    {
        $path = storage_path("app/hls_{$taskId}.mp4");
        return (file_exists($path) && filesize($path) > 1024) ? $path : null;
    }

    /**
     * Clean up all files for a task.
     */
    public function cleanupTask(string $taskId): void
    {
        @unlink(storage_path("app/hls_{$taskId}.mp4"));
        @unlink(storage_path("app/hls_{$taskId}.progress"));
        @unlink(storage_path("app/hls_{$taskId}.py"));
        @unlink(storage_path("app/hls_{$taskId}.mp4.txt")); // ffmpeg concat list

        // Remove segments directory if leftover
        $segDir = storage_path("app/hls_{$taskId}.mp4_segments");
        if (is_dir($segDir)) {
            $files = glob($segDir . '/*');
            foreach ($files as $f) {
                @unlink($f);
            }
            @rmdir($segDir);
        }
    }

    private function buildDownloadScript(string $url, string $outputPath): string
    {
        $packagesPath = env('PYTHON_PACKAGES_PATH', '');
        $ffmpegPath = 'C:\\xampp\\htdocs\\project1\\node_modules\\@ffmpeg-installer\\win32-x64\\ffmpeg.exe';
        $escapedUrl = addslashes($url);
        $escapedOutput = addslashes($outputPath);
        $escapedFfmpeg = addslashes($ffmpegPath);
        $progressPath = addslashes($outputPath . '/../' . basename(str_replace('.mp4', '.progress', $outputPath)));
        // Normalize to the same directory
        $progressFile = str_replace('.mp4', '.progress', $outputPath);
        $escapedProgress = addslashes($progressFile);

        return <<<PYTHON
import sys, re, os, subprocess, urllib.request, shutil, json
sys.path.insert(0, r'{$packagesPath}')
from concurrent.futures import ThreadPoolExecutor, as_completed

FFMPEG_PATH = r'{$escapedFfmpeg}'
URL = '{$escapedUrl}'
OUTPUT_PATH = r'{$escapedOutput}'
PROGRESS_FILE = r'{$escapedProgress}'
UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
MAX_WORKERS = 32


def write_progress(status, **kwargs):
    data = {'status': status, **kwargs}
    try:
        with open(PROGRESS_FILE, 'w') as f:
            json.dump(data, f)
    except Exception:
        pass


def rot_decode(text, shift):
    result = []
    for c in text:
        if c.isalpha():
            base = ord('A') if c.isupper() else ord('a')
            result.append(chr((ord(c) - base + shift) % 26 + base))
        else:
            result.append(c)
    return ''.join(result)


def fetch(url):
    req = urllib.request.Request(url, headers={'User-Agent': UA})
    with urllib.request.urlopen(req, timeout=30) as r:
        return r.read()


def download_segment(args):
    idx, url, seg_dir = args
    seg_path = os.path.join(seg_dir, f'{idx:06d}.ts')
    for attempt in range(3):
        try:
            req = urllib.request.Request(url, headers={'User-Agent': UA})
            with urllib.request.urlopen(req, timeout=30) as r:
                data = r.read()
            with open(seg_path, 'wb') as f:
                f.write(data)
            return idx, True
        except Exception:
            if attempt == 2:
                return idx, False
    return idx, False


def concat_to_mp4(seg_dir, total):
    write_progress('converting', message='Converting to MP4...')
    concat_file = OUTPUT_PATH + '.txt'
    with open(concat_file, 'w', encoding='utf-8') as f:
        for i in range(total):
            seg_path = os.path.join(seg_dir, f'{i:06d}.ts').replace('\\\\', '/')
            if os.path.exists(os.path.join(seg_dir, f'{i:06d}.ts')):
                f.write(f"file '{seg_path}'\\n")
    cmd = [
        FFMPEG_PATH, '-y',
        '-f', 'concat', '-safe', '0',
        '-i', concat_file,
        '-c', 'copy',
        '-movflags', '+faststart',
        '-bsf:a', 'aac_adtstoasc',
        OUTPUT_PATH
    ]
    result = subprocess.run(cmd, capture_output=True, timeout=300)
    if os.path.exists(concat_file):
        os.remove(concat_file)
    return result.returncode == 0 and os.path.exists(OUTPUT_PATH)


def parallel_download(segment_urls):
    total = len(segment_urls)
    write_progress('downloading', done=0, total=total, message=f'Downloading 0/{total} segments...')
    seg_dir = OUTPUT_PATH + '_segments'
    os.makedirs(seg_dir, exist_ok=True)
    try:
        tasks = [(i, u, seg_dir) for i, u in enumerate(segment_urls)]
        done_count = 0
        failed = 0
        with ThreadPoolExecutor(max_workers=MAX_WORKERS) as pool:
            futures = {pool.submit(download_segment, t): t[0] for t in tasks}
            for future in as_completed(futures):
                _, ok = future.result()
                if ok:
                    done_count += 1
                else:
                    failed += 1
                if (done_count + failed) % 50 == 0 or done_count + failed == total:
                    write_progress('downloading', done=done_count, total=total,
                                   message=f'Downloading {done_count}/{total} segments...')
                if failed > total * 0.1:
                    write_progress('error', message='Too many segment downloads failed.')
                    return False
        return concat_to_mp4(seg_dir, total)
    finally:
        if os.path.exists(seg_dir):
            shutil.rmtree(seg_dir, ignore_errors=True)


def try_tldv_download():
    write_progress('downloading', done=0, total=0, message='Fetching manifest...')
    content = fetch(URL).decode('utf-8', errors='replace')
    match = re.search(r'^#TLDVCONF:(\\d+),(\\d+),(.+?)$', content, re.MULTILINE)
    if not match:
        return False
    decode_shift = int(match.group(2))
    base_url = match.group(3).strip()
    segment_urls = []
    for line in content.split('\\n'):
        stripped = line.strip()
        if stripped and not stripped.startswith('#') and not stripped.startswith('http'):
            decoded = rot_decode(stripped, decode_shift)
            segment_urls.append(base_url + decoded)
    if not segment_urls:
        return False
    return parallel_download(segment_urls)


def try_hls_download():
    write_progress('downloading', done=0, total=0, message='Fetching manifest...')
    content = fetch(URL).decode('utf-8', errors='replace')
    if '#EXTM3U' not in content:
        return False
    segment_urls = []
    base = URL.rsplit('/', 1)[0] + '/'
    for line in content.split('\\n'):
        stripped = line.strip()
        if stripped and not stripped.startswith('#'):
            if stripped.startswith('http'):
                segment_urls.append(stripped)
            else:
                segment_urls.append(base + stripped)
    if not segment_urls:
        return False
    return parallel_download(segment_urls)


def try_ytdlp_download():
    write_progress('downloading', message='Trying yt-dlp...')
    import yt_dlp
    ydl_opts = {
        'quiet': True, 'no_warnings': True, 'noplaylist': True,
        'outtmpl': OUTPUT_PATH,
        'format': 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best',
        'merge_output_format': 'mp4',
        'ffmpeg_location': FFMPEG_PATH,
        'socket_timeout': 60,
        'http_headers': {'User-Agent': UA},
        'concurrent_fragment_downloads': 4,
    }
    try:
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            ydl.download([URL])
        return os.path.exists(OUTPUT_PATH)
    except Exception:
        return False


write_progress('starting', message='Initializing download...')
for method in [try_tldv_download, try_hls_download, try_ytdlp_download]:
    try:
        if method():
            write_progress('done', message='Download complete.')
            sys.exit(0)
    except Exception:
        pass

write_progress('error', message='All download methods failed.')
sys.exit(1)
PYTHON;
    }

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[\\\\\/\:\*\?\"\<\>\|]/', '_', $name);
        $name = preg_replace('/\s+/', '_', $name);
        return substr($name, 0, 80);
    }
}
