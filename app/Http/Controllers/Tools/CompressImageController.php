<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompressImageController extends Controller
{
    private const PYTHON_PATH = 'C:\\Python312\\python.exe';
    private const PACKAGES_PATH = 'C:\\xampp\\htdocs\\project1\\storage\\python-packages';

    public function index()
    {
        return view('tools.compress-image');
    }

    public function compress(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'quality' => 'required|integer|min:1|max:100',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'tiff'];
        if (!in_array($ext, $allowed)) {
            return response()->json(['error' => 'Unsupported image format: ' . $ext], 422);
        }

        $quality = (int) $request->input('quality', 75);
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $inputPath = $tempDir . '/' . uniqid('comp_in_') . '.' . $ext;
        $outputExt = in_array($ext, ['png', 'webp']) ? $ext : 'jpg';
        $outputFilename = uniqid('comp_out_') . '.' . $outputExt;
        $outputPath = $tempDir . '/' . $outputFilename;
        $file->move($tempDir, basename($inputPath));

        try {
            $script = $this->buildScript($inputPath, $outputPath, $outputExt, $quality);
            $scriptPath = $tempDir . '/' . uniqid('script_') . '.py';
            file_put_contents($scriptPath, $script);

            $cmd = sprintf('cmd /c ""%s" "%s" 2>nul"', self::PYTHON_PATH, $scriptPath);
            exec($cmd, $output, $returnCode);
            @unlink($scriptPath);

            if (!file_exists($outputPath)) {
                return response()->json(['error' => 'Compression failed.'], 500);
            }

            $originalSize = filesize($inputPath);
            $compressedSize = filesize($outputPath);
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            return response()->json([
                'downloadUrl' => '/tools/download/' . $outputFilename,
                'downloadName' => $originalName . '_compressed.' . $outputExt,
                'originalSize' => $originalSize,
                'compressedSize' => $compressedSize,
                'savedPercent' => $originalSize > 0 ? round((1 - $compressedSize / $originalSize) * 100, 1) : 0,
            ]);
        } finally {
            @unlink($inputPath);
        }
    }

    private function buildScript(string $input, string $output, string $ext, int $quality): string
    {
        $packagesPath = addslashes(self::PACKAGES_PATH);
        $input = addslashes($input);
        $output = addslashes($output);

        return <<<PYTHON
import sys
sys.path.insert(0, r'{$packagesPath}')
from PIL import Image

img = Image.open(r'{$input}')

# Convert to appropriate mode
if '{$ext}' == 'jpg' and img.mode in ('RGBA', 'P', 'LA'):
    bg = Image.new('RGB', img.size, (255, 255, 255))
    if img.mode == 'P':
        img = img.convert('RGBA')
    bg.paste(img, mask=img.split()[-1] if img.mode == 'RGBA' else None)
    img = bg
elif '{$ext}' == 'jpg':
    img = img.convert('RGB')

save_kwargs = {}
if '{$ext}' == 'jpg':
    save_kwargs['quality'] = {$quality}
    save_kwargs['optimize'] = True
elif '{$ext}' == 'png':
    save_kwargs['optimize'] = True
elif '{$ext}' == 'webp':
    save_kwargs['quality'] = {$quality}
    save_kwargs['method'] = 4

img.save(r'{$output}', **save_kwargs)
PYTHON;
    }
}
