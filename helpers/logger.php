<?php

declare(strict_types=1);

final class Logger
{
    public static function error(string $message, array $context = []): void
    {
        $config = require __DIR__ . '/../config/env.php';

        if (!$config['log']['enabled']) {
            return;
        }

        $logPath = $config['log']['path'];
        $logDir = dirname($logPath);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level'     => 'ERROR',
            'message'   => $message,
            'context'   => $context,
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'uri'       => $_SERVER['REQUEST_URI'] ?? '',
        ];

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
    }
}
