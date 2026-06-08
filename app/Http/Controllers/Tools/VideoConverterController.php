<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoConverterController extends Controller
{
    private const PYTHON_PATH = 'C:\\Python312\\python.exe';
    private const PACKAGES_PATH = 'C:\\xampp\\htdocs\\project1\\storage\\python-packages';

    private const ALLOWED_INPUTS = ['mp4', 'webm', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'm4v', '3gp', 'ts'];
    private const ALLOWED_OUTPUTS = ['mp4', 'webm', 'avi', 'mov', 'mkv'];
    private const MAX_SIZE = 200 * 1024 * 1024; // 200 MB

    public function index()
    {
        return view('tools.video-converter');
    }

    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:204800',
            'format' => 'required|string|in:' . implode(',', self::ALLOWED_OUTPUTS),
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, self::ALLOWED_INPUTS)) {
            return response()->json(['error' => 'Unsupported input format: ' . $ext], 422);
        }

        if ($file->getSize() > self::MAX_SIZE) {
            return response()->json(['error' => 'File too large. Maximum 200 MB.'], 422);
        }

        $targetFormat = $request->input('format');

        if ($ext === $targetFormat) {
            return response()->json(['error' => 'Input and output formats are the same.'], 422);
        }

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $inputPath = $tempDir . '/' . uniqid('vid_in_') . '.' . $ext;
        $outputFilename = uniqid('vid_out_') . '.' . $targetFormat;
        $outputPath = $tempDir . '/' . $outputFilename;
        $file->move($tempDir, basename($inputPath));

        try {
            $script = $this->buildScript($inputPath, $outputPath, $targetFormat);
            $scriptPath = $tempDir . '/' . uniqid('script_') . '.py';
            file_put_contents($scriptPath, $script);

            $python = env('PYTHON_EXECUTABLE', 'python');
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = sprintf('cmd /c ""%s" "%s" 2>nul"', $python, $scriptPath);
            } else {
                $cmd = sprintf('"%s" "%s" 2>/dev/null', $python, $scriptPath);
            }
            \exec($cmd, $output, $returnCode);
            @unlink($scriptPath);

            if (!file_exists($outputPath) || filesize($outputPath) === 0) {
                return response()->json(['error' => 'Conversion failed. The file format may not be supported.'], 500);
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            return response()->json([
                'downloadUrl' => '/tools/download/' . $outputFilename,
                'downloadName' => $originalName . '.' . $targetFormat,
                'originalSize' => filesize($inputPath),
                'convertedSize' => filesize($outputPath),
            ]);
        } finally {
            @unlink($inputPath);
        }
    }

    private function buildScript(string $input, string $output, string $format): string
    {
        $packagesPath = addslashes(env('PYTHON_PACKAGES_PATH', ''));
        $input = addslashes($input);
        $output = addslashes($output);

        $codecMap = [
            'mp4' => "-c:v libx264 -preset fast -crf 23 -c:a aac -b:a 192k -movflags +faststart",
            'webm' => "-c:v libvpx-vp9 -crf 30 -b:v 0 -c:a libopus -b:a 128k",
            'avi' => "-c:v mpeg4 -q:v 5 -c:a libmp3lame -b:a 192k",
            'mov' => "-c:v libx264 -preset fast -crf 23 -c:a aac -b:a 192k -movflags +faststart",
            'mkv' => "-c:v libx264 -preset fast -crf 23 -c:a aac -b:a 192k",
        ];

        $args = $codecMap[$format] ?? "-c:v libx264 -c:a aac";

        return <<<PYTHON
import sys, subprocess
sys.path.insert(0, r'{$packagesPath}')
import imageio_ffmpeg
ffmpeg = imageio_ffmpeg.get_ffmpeg_exe()
cmd = [ffmpeg, '-y', '-i', r'{$input}'] + '{$args}'.split() + [r'{$output}']
subprocess.run(cmd, capture_output=True, timeout=600)
PYTHON;
    }
}
