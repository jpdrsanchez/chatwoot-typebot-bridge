<?php

namespace Studio\Bridge\Support;

final class Logger
{
    private const LOG_FILE = __DIR__ . '/../../logs/logs.txt';

    private function __construct()
    {
    }

    public static function log(string $message): void
    {
        if ($_ENV['LOG'] !== 'true') {
            return;
        }

        $file = fopen(self::LOG_FILE, 'a+');
        if (! $file) {
            return;
        }

        $now     = date('c');
        $message = "[$now]: $message" . PHP_EOL;

        fputs($file, $message);
        fclose($file);
    }
}
