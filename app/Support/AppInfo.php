<?php

namespace App\Support;

class AppInfo
{
    private static ?string $commitHash = null;

    public static function commitHash(): string
    {
        if (static::$commitHash !== null) {
            return static::$commitHash;
        }

        $hash = '';

        if (function_exists('exec')) {
            $output = [];
            $code = 1;
            @exec('git -C '.escapeshellarg(base_path()).' rev-parse --short HEAD 2>&1', $output, $code);
            if ($code === 0 && ! empty($output) && is_string($output[0])) {
                $hash = trim($output[0]);
            }
        }

        if ($hash === '' && function_exists('shell_exec')) {
            $raw = @shell_exec('git -C '.escapeshellarg(base_path()).' rev-parse --short HEAD 2>&1');
            if (is_string($raw)) {
                $hash = trim($raw);
            }
        }

        return static::$commitHash = $hash;
    }
}