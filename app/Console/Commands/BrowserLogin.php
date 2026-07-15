<?php

namespace App\Console\Commands;

use App\Support\ProcessRunner;
use Illuminate\Console\Command;

/**
 * Opens a visible Chrome window using the persistent profile defined by
 * CHROME_USER_DATA_DIR, so you can log into a site (e.g. tl;dv) once. The
 * cookies persist in that profile, after which the headless scanner can render
 * login-gated pages as the signed-in user.
 *
 * Local/desktop use only — there is no interactive display on the server.
 */
class BrowserLogin extends Command
{
    protected $signature = 'browser:login {url=https://tldv.io/login : URL to open for logging in}';

    protected $description = 'Open a visible Chrome with the persistent profile so you can log in once';

    public function handle(): int
    {
        $userDataDir = config('tools.chrome_user_data_dir');
        if (!$userDataDir) {
            $this->error('CHROME_USER_DATA_DIR is not set in your .env. Set it to a folder path (e.g. storage/app/chrome-profile) and run `php artisan config:clear`.');
            return self::FAILURE;
        }

        if (!is_dir($userDataDir)) {
            @mkdir($userDataDir, 0755, true);
        }

        $chrome = config('tools.chrome_path');
        if (!$chrome || !is_file($chrome)) {
            $this->error('CHROME_PATH is not set or does not point to a real chrome.exe. Set it in .env.');
            return self::FAILURE;
        }

        $url = $this->argument('url');

        $this->info('Opening Chrome with profile: ' . $userDataDir);
        $this->line('1. Log into the site in the window that opens.');
        $this->line('2. Once you can see your content, CLOSE the Chrome window.');
        $this->line('3. The login is now saved; the scanner will reuse it.');

        // Launch visible (not headless) with the persistent profile.
        $cmd = sprintf(
            '"%s" --user-data-dir="%s" --no-first-run --no-default-browser-check "%s"',
            $chrome,
            $userDataDir,
            $url
        );

        // Blocks until the user closes the Chrome window.
        ProcessRunner::run($cmd, 1800);

        $this->info('Chrome closed. Login session saved to the profile.');
        return self::SUCCESS;
    }
}
