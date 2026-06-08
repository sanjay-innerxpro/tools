<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MergePdfController extends Controller
{
    private const PYTHON_PATH = 'C:\\Python312\\python.exe';
    private const PACKAGES_PATH = 'C:\\xampp\\htdocs\\project1\\storage\\python-packages';

    public function index()
    {
        return view('tools.merge-pdf');
    }

    public function merge(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array|min:2|max:20',
            'files.*' => 'required|file|mimes:pdf|max:51200',
        ]);

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $inputPaths = [];
        foreach ($request->file('files') as $file) {
            $path = $tempDir . '/' . uniqid('merge_in_') . '.pdf';
            $file->move($tempDir, basename($path));
            $inputPaths[] = $path;
        }

        $outputFilename = uniqid('merged_') . '.pdf';
        $outputPath = $tempDir . '/' . $outputFilename;

        try {
            $script = $this->buildScript($inputPaths, $outputPath);
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

            if (!file_exists($outputPath)) {
                return response()->json(['error' => 'Failed to merge PDFs.'], 500);
            }

            return response()->json([
                'downloadUrl' => '/tools/download/' . $outputFilename,
                'downloadName' => 'merged.pdf',
                'fileSize' => filesize($outputPath),
                'fileCount' => count($inputPaths),
            ]);
        } finally {
            foreach ($inputPaths as $path) {
                @unlink($path);
            }
        }
    }

    private function buildScript(array $inputPaths, string $outputPath): string
    {
        $packagesPath = addslashes(env('PYTHON_PACKAGES_PATH', ''));
        $output = addslashes($outputPath);

        $pathsList = implode("',\n    r'", array_map('addslashes', $inputPaths));

        return <<<PYTHON
import sys
sys.path.insert(0, r'{$packagesPath}')
from PyPDF2 import PdfMerger

merger = PdfMerger()
paths = [
    r'{$pathsList}'
]

for path in paths:
    merger.append(path)

merger.write(r'{$output}')
merger.close()
PYTHON;
    }
}
