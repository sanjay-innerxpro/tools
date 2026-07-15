<?php

namespace App\Support;

/**
 * Runs external commands using whichever process-spawning function the host
 * actually permits. Shared hosts frequently disable exec()/shell_exec() but
 * leave proc_open() (and sometimes popen()) enabled, so we probe in order of
 * usefulness and adapt instead of hard-failing on a single missing function.
 */
class ProcessRunner
{
    /** Synchronous run candidates, most capable first. */
    private const RUN_FUNCS = ['proc_open', 'popen', 'exec', 'shell_exec'];

    /** Returns the first usable run function, or null if the host blocks them all. */
    public static function method(): ?string
    {
        foreach (self::RUN_FUNCS as $fn) {
            if (self::enabled($fn)) {
                return $fn;
            }
        }
        return null;
    }

    public static function available(): bool
    {
        return self::method() !== null;
    }

    private static function enabled(string $fn): bool
    {
        if (!function_exists($fn)) {
            return false;
        }
        $disabled = array_map('trim', explode(',', strtolower((string) ini_get('disabled_functions'))));
        return !in_array(strtolower($fn), $disabled, true);
    }

    /**
     * Run a command synchronously.
     *
     * @return array{0: string[], 1: int}  [output lines, exit code]
     * @throws \RuntimeException if the host disables every spawn function.
     */
    public static function run(string $cmd, int $timeoutSeconds = 300): array
    {
        $method = self::method();

        return match ($method) {
            'proc_open'  => self::viaProcOpen($cmd, $timeoutSeconds),
            'popen'      => self::viaPopen($cmd),
            'exec'       => self::viaExec($cmd),
            'shell_exec' => self::viaShellExec($cmd),
            default      => throw new \RuntimeException(
                'No process-execution function is available on this host '
                . '(exec, shell_exec, proc_open and popen are all disabled).'
            ),
        };
    }

    /**
     * Start a command in the background and return immediately (best effort).
     * Falls back to a blocking run if no fire-and-forget primitive is available.
     */
    public static function runBackground(string $cmd): void
    {
        if (self::enabled('popen')) {
            $h = @popen($cmd, 'r');
            if (is_resource($h)) {
                @pclose($h);
            }
            return;
        }

        if (self::enabled('proc_open')) {
            $proc = @proc_open($cmd, [['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']], $pipes);
            if (is_resource($proc)) {
                if (isset($pipes[0]) && is_resource($pipes[0])) {
                    fclose($pipes[0]);
                }
                // Do not proc_close() — that would block on the child.
            }
            return;
        }

        // Last resort: run it synchronously (caller already appended a backgrounding
        // operator like `&`, so on most shells this still returns quickly).
        self::run($cmd);
    }

    private static function viaProcOpen(string $cmd, int $timeoutSeconds): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($proc)) {
            return [[], 1];
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $start = time();

        while (true) {
            $status = proc_get_status($proc);
            $stdout .= stream_get_contents($pipes[1]);
            // Drain stderr so the child doesn't block on a full pipe.
            stream_get_contents($pipes[2]);

            if (!$status['running']) {
                break;
            }

            if (time() - $start > $timeoutSeconds) {
                proc_terminate($proc);
                break;
            }

            usleep(50000); // 50ms
        }

        $stdout .= stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exit = proc_close($proc);
        if (isset($status) && !$status['running'] && isset($status['exitcode']) && $status['exitcode'] >= 0) {
            $exit = $status['exitcode'];
        }

        return [self::lines($stdout), $exit];
    }

    private static function viaPopen(string $cmd): array
    {
        $h = popen($cmd, 'r');
        if (!is_resource($h)) {
            return [[], 1];
        }
        $out = '';
        while (!feof($h)) {
            $out .= fread($h, 8192);
        }
        $exit = pclose($h);
        return [self::lines($out), is_int($exit) ? $exit : 0];
    }

    private static function viaExec(string $cmd): array
    {
        $out = [];
        $rc = 0;
        exec($cmd, $out, $rc);
        return [$out, $rc];
    }

    private static function viaShellExec(string $cmd): array
    {
        $out = shell_exec($cmd);
        return [self::lines((string) $out), 0];
    }

    private static function lines(string $text): array
    {
        $text = rtrim($text, "\r\n");
        return $text === '' ? [] : explode("\n", str_replace("\r\n", "\n", $text));
    }
}
