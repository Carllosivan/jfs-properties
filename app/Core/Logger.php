<?php

namespace App\Core;

class Logger
{
    private static string $logDir = __DIR__ . '/../../storage/logs';

    public static function log(string $level, string $message, array $context = []): void
    {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        $file = self::$logDir . "/app-{$date}.log";

        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $entry = "[{$time}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }
}
