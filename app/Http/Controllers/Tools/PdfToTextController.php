<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Support\ProcessRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PdfToTextController extends Controller
{
    private const PYTHON_PATH = 'C:\\Python312\\python.exe';
    private const PACKAGES_PATH = 'C:\\xampp\\htdocs\\project1\\storage\\python-packages';

    public function index()
    {
        return view('tools.pdf-to-text');
    }

    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:51200',
        ]);

        $file = $request->file('file');
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $inputPath = $tempDir . '/' . uniqid('pdf_') . '.pdf';
        $outputPath = $tempDir . '/' . uniqid('txt_') . '.json';
        $file->move($tempDir, basename($inputPath));

        try {
            $script = $this->buildScript($inputPath, $outputPath);
            $scriptPath = $tempDir . '/' . uniqid('script_') . '.py';
            file_put_contents($scriptPath, $script);

            $python = env('PYTHON_EXECUTABLE', 'python');
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = sprintf('cmd /c ""%s" "%s" 2>nul"', $python, $scriptPath);
            } else {
                $cmd = sprintf('"%s" "%s" 2>/dev/null', $python, $scriptPath);
            }
            ProcessRunner::run($cmd, 300);

            @unlink($scriptPath);

            if (!file_exists($outputPath)) {
                return response()->json(['error' => 'Failed to extract text from PDF.'], 500);
            }

            $result = json_decode(file_get_contents($outputPath), true);
            @unlink($outputPath);

            if (!$result || isset($result['error'])) {
                return response()->json(['error' => $result['error'] ?? 'Extraction failed.'], 500);
            }

            // Save text file for download
            $txtFilename = uniqid('result_') . '.txt';
            $txtPath = $tempDir . '/' . $txtFilename;
            file_put_contents($txtPath, $result['text']);

            return response()->json([
                'text' => $result['text'],
                'pages' => $result['pages'],
                'chars' => mb_strlen($result['text']),
                'downloadUrl' => '/tools/download/' . $txtFilename,
                'downloadName' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.txt',
            ]);
        } finally {
            @unlink($inputPath);
        }
    }

    private function buildScript(string $inputPath, string $outputPath): string
    {
        $packagesPath = addslashes(env('PYTHON_PACKAGES_PATH', ''));
        $input = addslashes($inputPath);
        $output = addslashes($outputPath);

        return <<<PYTHON
import sys, json
sys.path.insert(0, r'{$packagesPath}')
import pdfplumber

try:
    text = ""
    page_count = 0
    with pdfplumber.open(r'{$input}') as pdf:
        page_count = len(pdf.pages)
        for page in pdf.pages:
            page_text = page.extract_text()
            if page_text:
                text += page_text + "\\n\\n"
    with open(r'{$output}', 'w', encoding='utf-8') as f:
        json.dump({'text': text.strip(), 'pages': page_count}, f, ensure_ascii=False)
except Exception as e:
    with open(r'{$output}', 'w', encoding='utf-8') as f:
        json.dump({'error': str(e)}, f)
PYTHON;
    }
}
