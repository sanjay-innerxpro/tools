<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Support\ProcessRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageConverterController extends Controller
{
    private const PYTHON_PATH = 'C:\\Python312\\python.exe';
    private const PACKAGES_PATH = 'C:\\xampp\\htdocs\\project1\\storage\\python-packages';

    private const ALLOWED_INPUTS = ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif', 'tiff', 'ico'];
    private const ALLOWED_OUTPUTS = ['png', 'jpg', 'webp', 'bmp', 'gif', 'ico', 'tiff', 'pdf'];

    public function index()
    {
        return view('tools.image-converter');
    }

    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'format' => 'required|string|in:' . implode(',', self::ALLOWED_OUTPUTS),
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, self::ALLOWED_INPUTS)) {
            return response()->json(['error' => 'Unsupported input format: ' . $ext], 422);
        }

        $targetFormat = $request->input('format');
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $inputPath = $tempDir . '/' . uniqid('img_in_') . '.' . $ext;
        $outputFilename = uniqid('img_out_') . '.' . $targetFormat;
        $outputPath = $tempDir . '/' . $outputFilename;
        $file->move($tempDir, basename($inputPath));

        try {
            $script = $this->buildScript($inputPath, $outputPath, $targetFormat);
            $scriptPath = $tempDir . '/' . uniqid('script_') . '.py';
            file_put_contents($scriptPath, $script);

            $python = config('tools.python');
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = sprintf('cmd /c ""%s" "%s" 2>nul"', $python, $scriptPath);
            } else {
                $cmd = sprintf('"%s" "%s" 2>/dev/null', $python, $scriptPath);
            }
            ProcessRunner::run($cmd, 300);
            @unlink($scriptPath);

            if (!file_exists($outputPath)) {
                return response()->json(['error' => 'Conversion failed.'], 500);
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
        $packagesPath = addslashes(config('tools.python_packages_path'));
        $input = addslashes($input);
        $output = addslashes($output);

        $mode = in_array($format, ['jpg', 'bmp', 'pdf', 'ico']) ? 'RGB' : 'RGBA';

        return <<<PYTHON
import sys
sys.path.insert(0, r'{$packagesPath}')
from PIL import Image

img = Image.open(r'{$input}')
if img.mode != '{$mode}' and '{$mode}' == 'RGB':
    img = img.convert('RGB')
elif img.mode not in ('RGBA', 'RGB'):
    img = img.convert('RGBA')

save_kwargs = {}
if '{$format}' == 'jpg':
    save_kwargs['quality'] = 95
elif '{$format}' == 'webp':
    save_kwargs['quality'] = 90
elif '{$format}' == 'png':
    save_kwargs['optimize'] = True

img.save(r'{$output}', **save_kwargs)
PYTHON;
    }
}
