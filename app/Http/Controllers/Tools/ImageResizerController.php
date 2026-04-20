<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageResizerController extends Controller
{
    private const PYTHON = 'C:\\Python312\\python.exe';
    private const PKGS   = 'C:\\xampp\\htdocs\\project1\\storage\\python-packages';

    public function index()
    {
        return view('tools.image-resizer');
    }

    public function resize(Request $request): JsonResponse
    {
        $request->validate([
            'file'   => 'required|file|max:51200',
            'mode'   => 'required|in:dimensions,percentage',
            'width'  => 'nullable|integer|min:1|max:10000',
            'height' => 'nullable|integer|min:1|max:10000',
            'percent'=> 'nullable|integer|min:1|max:500',
            'keep_aspect' => 'nullable|in:0,1',
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());
        $allowed = ['jpg','jpeg','png','webp','bmp','gif','tiff'];

        if (!in_array($ext, $allowed)) {
            return response()->json(['error' => 'Unsupported format: ' . $ext], 422);
        }

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $inputPath      = $tempDir . '/' . uniqid('rsz_in_') . '.' . $ext;
        $outputFilename = uniqid('rsz_out_') . '.' . $ext;
        $outputPath     = $tempDir . '/' . $outputFilename;
        $file->move($tempDir, basename($inputPath));

        $mode       = $request->input('mode');
        $width      = (int) $request->input('width', 0);
        $height     = (int) $request->input('height', 0);
        $percent    = (int) $request->input('percent', 100);
        $keepAspect = $request->input('keep_aspect', '1');

        try {
            $script     = $this->buildScript($inputPath, $outputPath, $ext, $mode, $width, $height, $percent, $keepAspect);
            $scriptPath = $tempDir . '/' . uniqid('s_') . '.py';
            file_put_contents($scriptPath, $script);

            $cmd = sprintf('cmd /c ""%s" "%s" 2>nul"', self::PYTHON, $scriptPath);
            exec($cmd, $out, $rc);
            @unlink($scriptPath);

            if (!file_exists($outputPath)) {
                return response()->json(['error' => 'Resize failed.'], 500);
            }

            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            return response()->json([
                'downloadUrl'    => '/tools/download/' . $outputFilename,
                'downloadName'   => $origName . '_resized.' . $ext,
                'originalSize'   => filesize($inputPath),
                'resizedSize'    => filesize($outputPath),
            ]);
        } finally {
            @unlink($inputPath);
        }
    }

    private function buildScript(string $in, string $out, string $ext, string $mode, int $w, int $h, int $pct, string $keep): string
    {
        $pkgs = addslashes(self::PKGS);
        $in   = addslashes($in);
        $out  = addslashes($out);

        return <<<PYTHON
import sys
sys.path.insert(0, r'{$pkgs}')
from PIL import Image

img = Image.open(r'{$in}')
ow, oh = img.size

if '{$mode}' == 'percentage':
    nw = int(ow * {$pct} / 100)
    nh = int(oh * {$pct} / 100)
else:
    tw, th = {$w}, {$h}
    if '{$keep}' == '1':
        if tw and th:
            ratio = min(tw / ow, th / oh)
            nw, nh = int(ow * ratio), int(oh * ratio)
        elif tw:
            nw = tw
            nh = int(oh * tw / ow)
        else:
            nh = th
            nw = int(ow * th / oh)
    else:
        nw = tw if tw else ow
        nh = th if th else oh

img = img.resize((nw, nh), Image.LANCZOS)

save_kw = {}
if '{$ext}' in ('jpg', 'jpeg'):
    if img.mode != 'RGB':
        img = img.convert('RGB')
    save_kw['quality'] = 95
elif '{$ext}' == 'webp':
    save_kw['quality'] = 90
elif '{$ext}' == 'png':
    save_kw['optimize'] = True

img.save(r'{$out}', **save_kw)
PYTHON;
    }
}
