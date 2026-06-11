<?php

declare(strict_types=1);

/**
 * JWT authentication placeholder for future API protection.
 * Set JWT_ENABLED=true in environment when ready to enforce tokens.
 */
final class AuthHelper
{
    public static function enforce(): void
    {
        $config = require __DIR__ . '/../config/env.php';

        if (!$config['app']['jwt_enabled']) {
            return;
        }

        $headerName = $config['app']['jwt_header'];
        $token = self::extractBearerToken($headerName);

        if ($token === null || !self::validateToken($token, $config)) {
            ApiResponse::error('Unauthorized access.', 401);
        }
    }

    public static function extractBearerToken(string $headerName): ?string
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));
        $authHeader = $_SERVER[$headerKey] ?? '';

        if ($authHeader === '' && function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers[$headerName] ?? $headers[strtolower($headerName)] ?? '';
        }

        if (!preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            return null;
        }

        return $matches[1];
    }

    public static function validateToken(string $token, ?array $config = null): bool
    {
        $config ??= require __DIR__ . '/../config/env.php';
        $secret = $config['app']['jwt_secret'] ?? '';

        if ($secret === '') {
            Logger::error('JWT enabled but secret is missing');

            return false;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', $headerB64 . '.' . $payloadB64, $secret, true)
        );

        if (!hash_equals($expectedSignature, $signatureB64)) {
            return false;
        }

        $payloadJson = self::base64UrlDecode($payloadB64);
        $payload = json_decode($payloadJson, true);

        if (!is_array($payload)) {
            return false;
        }

        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            return false;
        }

        return true;
    }

    public static function generateToken(array $claims, int $ttlSeconds = 3600): string
    {
        $config = require __DIR__ . '/../config/env.php';
        $secret = $config['app']['jwt_secret'] ?? '';

        if ($secret === '') {
            throw new RuntimeException('JWT secret is not configured.');
        }

        $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payloadData = array_merge($claims, [
            'iat' => time(),
            'exp' => time() + $ttlSeconds,
        ]);
        $payload = self::base64UrlEncode(json_encode($payloadData));
        $signature = self::base64UrlEncode(
            hash_hmac('sha256', $header . '.' . $payload, $secret, true)
        );

        return $header . '.' . $payload . '.' . $signature;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($data, '-_', '+/'), true);
    }
}
