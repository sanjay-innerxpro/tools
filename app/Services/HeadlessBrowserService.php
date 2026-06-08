<?php

namespace App\Services;

use App\Support\ProcessRunner;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class HeadlessBrowserService
{
    private const CHROME_PATH = 'C:\\Users\\Innerx\\.cache\\puppeteer\\chrome\\win64-146.0.7680.153\\chrome-win64\\chrome.exe';
    private const NODE_PATH = 'C:\\Program Files\\nodejs\\node.exe';
    private const NPM_PATH = 'C:\\xampp\\htdocs\\project1\\node_modules';
    private const PROJECT_ROOT = 'C:\\xampp\\htdocs\\project1';

    private const MEDIA_EXTENSIONS = [
        'mp4', 'webm', 'ogg', 'ogv', 'avi', 'mov', 'mkv', 'flv', 'wmv',
        'm3u8', 'mpd', 'mp3', 'wav', 'aac', 'flac', 'm4a', 'opus',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar',
    ];

    private const MEDIA_MIME_PREFIXES = [
        'video/', 'audio/', 'application/pdf',
        'application/x-mpegurl', 'application/dash+xml',
    ];

    public function render(string $url): array
    {
        // Run unified intercept script (gets HTML + intercepts network)
        $result = $this->interceptNetworkRequests($url);

        if ($result['success'] && !empty($result['html'])) {
            return $result;
        }

        // Fallback to Browsershot if intercept failed
        try {
            $html = $this->getRenderedHtml($url);
            return [
                'success' => true,
                'html' => $html,
                'intercepted_urls' => $result['intercepted_urls'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::warning('HeadlessBrowser render failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'html' => '',
                'intercepted_urls' => [],
            ];
        }
    }

    private function getRenderedHtml(string $url): string
    {
        $browsershot = Browsershot::url($url);

        // Only pin binary paths when explicitly configured. On Linux/shared hosting
        // we let Browsershot find `node` on PATH and use Puppeteer's bundled Chromium,
        // instead of the Windows dev-machine defaults which don't exist on the server.
        $chromePath = config('tools.chrome_path') ?: null;
        $nodePath = config('tools.node_binary') ?: null;
        $npmPath = config('tools.npm_binary') ?: null;

        if (PHP_OS_FAMILY === 'Windows') {
            $chromePath = $chromePath ?: self::CHROME_PATH;
            $nodePath = $nodePath ?: self::NODE_PATH;
            $npmPath = $npmPath ?: self::NPM_PATH;
        }

        if ($chromePath) $browsershot->setChromePath($chromePath);
        if ($nodePath) $browsershot->setNodeBinary($nodePath);
        if ($npmPath) $browsershot->setNpmBinary($npmPath);

        return $browsershot
            ->noSandbox()
            ->dismissDialogs()
            ->setOption('args', [
                '--disable-gpu',
                '--disable-dev-shm-usage',
                '--disable-web-security',
                '--no-first-run',
            ])
            ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
            ->setOption('waitUntil', 'domcontentloaded')
            ->setDelay(5000)
            ->timeout(45)
            ->bodyHtml();
    }

    private function interceptNetworkRequests(string $url): array
    {
        $scriptPath = $this->writeInterceptScript();
        $outputFile = storage_path('app/intercept_out_' . uniqid() . '.json');

        try {
            $this->runNodeScript($scriptPath, $url, $outputFile);

            if (file_exists($outputFile)) {
                $raw = file_get_contents($outputFile);
                @unlink($outputFile);
                $data = json_decode($raw, true);
            } else {
                throw new \RuntimeException('Intercept script produced no output file.');
            }

            if (!is_array($data)) {
                throw new \RuntimeException('Invalid JSON from intercept script.');
            }

            return [
                'success' => true,
                'html' => $data['html'] ?? '',
                'intercepted_urls' => $data['media_urls'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::warning('Network interception failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            @unlink($outputFile);
            return ['success' => false, 'html' => '', 'intercepted_urls' => []];
        } finally {
            @unlink($scriptPath);
        }
    }

    private function writeInterceptScript(): string
    {
        // Chrome path: prefer config; empty string lets Puppeteer use its bundled Chromium.
        $chromePath = addslashes((string) (config('tools.chrome_path')
            ?: (PHP_OS_FAMILY === 'Windows' ? self::CHROME_PATH : '')));
        // node_modules location that holds the puppeteer package.
        $defaultModules = PHP_OS_FAMILY === 'Windows'
            ? self::PROJECT_ROOT . '\\node_modules'
            : base_path('node_modules');
        $modulePath = addslashes((string) (config('tools.node_modules_path') ?: $defaultModules));
        $mediaExts = json_encode(self::MEDIA_EXTENSIONS);
        $mediaMimes = json_encode(self::MEDIA_MIME_PREFIXES);

        $script = <<<'JSEOF'
'use strict';
const path = require('path');
const fs = require('fs');

// Ensure puppeteer resolves from project node_modules
const modulePath = '__MODULE_PATH__';
module.paths.unshift(modulePath);

const puppeteer = require('puppeteer');

const mediaExts = __MEDIA_EXTS__;
const mediaMimes = __MEDIA_MIMES__;
const targetUrl = process.argv[2];
const outputFile = process.argv[3];

function isMediaUrl(url, ct) {
    if (url.startsWith('blob:') || url.startsWith('data:')) return false;
    try {
        const p = new URL(url).pathname.toLowerCase().split('?')[0];
        const ext = p.split('.').pop();
        if (mediaExts.includes(ext)) return true;
    } catch {}
    if (/(\.m3u8|\.mpd)(\?|$)/i.test(url)) return true;
    if (ct) {
        const c = ct.toLowerCase();
        for (const pfx of mediaMimes) { if (c.startsWith(pfx)) return true; }
    }
    return false;
}

(async () => {
    const mediaUrls = [], seen = new Set();
    let browser, html = '';
    try {
        const launchOpts = {
            headless: 'new',
            args: ['--no-sandbox','--disable-gpu','--disable-dev-shm-usage','--disable-web-security','--no-first-run']
        };
        const chromePath = '__CHROME_PATH__';
        if (chromePath) { launchOpts.executablePath = chromePath; }
        browser = await puppeteer.launch(launchOpts);
        const page = await browser.newPage();
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        page.on('response', async (res) => {
            try {
                const u = res.url(); if (seen.has(u)) return;
                const ct = res.headers()['content-type'] || '';
                const s = res.status();
                if (s >= 200 && s < 400 && isMediaUrl(u, ct)) { seen.add(u); mediaUrls.push({url:u,content_type:ct,status:s}); }
            } catch {}
        });
        page.on('request', (req) => {
            try {
                const u = req.url(); if (seen.has(u)) return;
                const rt = req.resourceType();
                if ((rt === 'media' || rt === 'fetch' || rt === 'xhr') && isMediaUrl(u, '')) { seen.add(u); mediaUrls.push({url:u,content_type:null,status:null}); }
            } catch {}
        });

        await page.goto(targetUrl, {waitUntil:'domcontentloaded', timeout:30000});
        await new Promise(r => setTimeout(r, 6000));
        await page.evaluate(async () => {
            await new Promise(resolve => {
                let y = 0;
                const t = setInterval(() => {
                    window.scrollBy(0, 400); y += 400;
                    if (y >= Math.min(document.body.scrollHeight, 4000)) { clearInterval(t); resolve(); }
                }, 150);
            });
        }).catch(() => {});
        await new Promise(r => setTimeout(r, 2000));
        html = await page.content();
    } catch (err) {
        fs.writeFileSync(outputFile, JSON.stringify({html:'',media_urls:mediaUrls,error:err.message}));
        if (browser) await browser.close().catch(()=>{});
        process.exit(0);
    }
    if (browser) await browser.close().catch(()=>{});
    fs.writeFileSync(outputFile, JSON.stringify({html, media_urls:mediaUrls}));
    process.exit(0);
})();
JSEOF;

        // Substitute constants into the script
        $script = str_replace('__MODULE_PATH__', $modulePath, $script);
        $script = str_replace('__CHROME_PATH__', $chromePath, $script);
        $script = str_replace('__MEDIA_EXTS__', $mediaExts, $script);
        $script = str_replace('__MEDIA_MIMES__', $mediaMimes, $script);

        $tempPath = storage_path('app/intercept_' . uniqid() . '.cjs');
        $dir = dirname($tempPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($tempPath, $script);

        return $tempPath;
    }

    private function runNodeScript(string $scriptPath, string $url, string $outputFile): void
    {
        $node = config('tools.node_binary') ?: (PHP_OS_FAMILY === 'Windows' ? self::NODE_PATH : 'node');
        $projectRoot = config('tools.node_project_root') ?: (PHP_OS_FAMILY === 'Windows' ? self::PROJECT_ROOT : base_path());

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = sprintf(
                'cmd /c "cd /d "%s" && "%s" "%s" "%s" "%s" 2>nul"',
                $projectRoot,
                $node,
                $scriptPath,
                $url,
                $outputFile
            );
        } else {
            $cmd = sprintf(
                'cd %s && "%s" "%s" "%s" "%s" 2>/dev/null',
                escapeshellarg($projectRoot),
                $node,
                $scriptPath,
                $url,
                $outputFile
            );
        }

        // Routes through whatever spawn function the host allows; throws a catchable
        // RuntimeException if all are disabled, so the scan falls back to yt-dlp.
        ProcessRunner::run($cmd, 60);
    }
}

