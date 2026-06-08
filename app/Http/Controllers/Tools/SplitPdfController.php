<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SplitPdfController extends Controller
{
    private const PYTHON = 'C:\\Python312\\python.exe';
    private const PKGS   = 'C:\\xampp\\htdocs\\project1\\storage\\python-packages';

    public function index()
    {
        return view('tools.split-pdf');
    }

    public function split(Request $request): JsonResponse
    {
        $request->validate([
            'file'  => 'required|file|mimes:pdf|max:51200',
            'mode'  => 'required|in:all,range',
            'from'  => 'nullable|integer|min:1',
            'to'    => 'nullable|integer|min:1',
        ]);

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $file      = $request->file('file');
        $inputPath = $tempDir . '/' . uniqid('split_in_') . '.pdf';
        $file->move($tempDir, basename($inputPath));

        $mode = $request->input('mode', 'all');
        $from = (int) $request->input('from', 1);
        $to   = (int) $request->input('to', 0);

        $outputJson = $tempDir . '/' . uniqid('split_out_') . '.json';

        try {
            $script     = $this->buildScript($inputPath, $tempDir, $outputJson, $mode, $from, $to);
            $scriptPath = $tempDir . '/' . uniqid('s_') . '.py';
            file_put_contents($scriptPath, $script);

            $python = env('PYTHON_EXECUTABLE', 'python');
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = sprintf('cmd /c ""%s" "%s" 2>nul"', $python, $scriptPath);
            } else {
                $cmd = sprintf('"%s" "%s" 2>/dev/null', $python, $scriptPath);
            }
            \exec($cmd, $out, $rc);
            @unlink($scriptPath);

            if (!file_exists($outputJson)) {
                return response()->json(['error' => 'Split failed.'], 500);
            }

            $result = json_decode(file_get_contents($outputJson), true);
            @unlink($outputJson);

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 422);
            }

            return response()->json($result);
        } finally {
            @unlink($inputPath);
        }
    }

    private function buildScript(string $input, string $tempDir, string $outputJson, string $mode, int $from, int $to): string
    {
        $pkgs = addslashes(env('PYTHON_PACKAGES_PATH', ''));
        $in   = addslashes($input);
        $dir  = addslashes($tempDir);
        $out  = addslashes($outputJson);

        return <<<PYTHON
import sys, json, os
sys.path.insert(0, r'{$pkgs}')
from PyPDF2 import PdfReader, PdfWriter

try:
    reader = PdfReader(r'{$in}')
    total  = len(reader.pages)

    if '{$mode}' == 'range':
        fr, to = {$from}, {$to} if {$to} > 0 else total
        if fr > total:
            raise ValueError(f'Start page {{fr}} exceeds total {{total}} pages')
        to = min(to, total)
        ranges = [(fr - 1, to)]
    else:
        ranges = [(i, i + 1) for i in range(total)]

    files = []
    for start, end in ranges:
        w = PdfWriter()
        for p in range(start, end):
            w.add_page(reader.pages[p])
        if len(ranges) == 1:
            name = f'pages_{start+1}-{end}.pdf'
        else:
            name = f'page_{start+1}.pdf'
        fpath = os.path.join(r'{$dir}', f'sp_{os.urandom(8).hex()}_{name}')
        with open(fpath, 'wb') as f:
            w.write(f)
        files.append({
            'name': name,
            'downloadUrl': '/tools/download/' + os.path.basename(fpath),
            'size': os.path.getsize(fpath)
        })

    with open(r'{$out}', 'w') as f:
        json.dump({'files': files, 'totalPages': total}, f)
except Exception as e:
    with open(r'{$out}', 'w') as f:
        json.dump({'error': str(e)}, f)
PYTHON;
    }
}
