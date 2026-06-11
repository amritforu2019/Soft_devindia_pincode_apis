<?php

declare(strict_types=1);

final class ApiResponse
{
    public static function success(array $data, string $message = 'Success', int $httpCode = 200): never
    {
        self::send([
            'status'  => true,
            'message' => $message,
            'count'   => count($data),
            'data'    => $data,
        ], $httpCode);
    }

    public static function error(string $message, int $httpCode = 400): never
    {
        self::send([
            'status'  => false,
            'message' => $message,
        ], $httpCode);
    }

    private static function send(array $payload, int $httpCode): never
    {
        if (!headers_sent()) {
            http_response_code($httpCode);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        }

        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
        $json = json_encode($payload, $jsonFlags);

        if (self::supportsGzip()) {
            header('Content-Encoding: gzip');
            echo gzencode($json, 6);
        } else {
            echo $json;
        }

        exit;
    }

    private static function supportsGzip(): bool
    {
        $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';

        return str_contains($acceptEncoding, 'gzip') && function_exists('gzencode');
    }
}
