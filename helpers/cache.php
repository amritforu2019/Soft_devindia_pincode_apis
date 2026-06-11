<?php

declare(strict_types=1);

final class CacheHelper
{
    private static ?array $config = null;

    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/env.php';
        }

        return self::$config;
    }

    public static function buildKey(string $key): string
    {
        $prefix = self::config()['redis']['prefix'] ?? 'LOC:';

        return $prefix . $key;
    }

    public static function get(string $key): ?array
    {
        try {
            $redis = RedisClient::getConnection();
            $value = $redis->get(self::buildKey($key));

            return is_array($value) ? $value : null;
        } catch (Throwable $e) {
            Logger::error('Redis read failed', ['key' => $key, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public static function set(string $key, array $data): bool
    {
        try {
            $redis = RedisClient::getConnection();
            $ttl = self::config()['redis']['ttl'] ?? 86400;

            return (bool) $redis->setex(self::buildKey($key), $ttl, $data);
        } catch (Throwable $e) {
            Logger::error('Redis write failed', ['key' => $key, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public static function remember(string $key, callable $callback): array
    {
        $cached = self::get($key);

        if ($cached !== null) {
            return $cached;
        }

        $data = $callback();

        if (is_array($data)) {
            self::set($key, $data);
        }

        return $data;
    }

    public static function keyForStates(): string
    {
        return 'STATE_LIST';
    }

    public static function keyForCities(int $stateId): string
    {
        return 'CITY_' . $stateId;
    }

    public static function keyForAreas(int $cityId): string
    {
        return 'AREA_' . $cityId;
    }

    public static function keyForPincode(string $pincode): string
    {
        return 'PIN_' . $pincode;
    }

    public static function keyForSearchById(string $type, int $id): string
    {
        return match ($type) {
            'state_id' => 'SEARCH_STATE_' . $id,
            'city_id'  => 'SEARCH_CITY_' . $id,
            'area_id'  => 'SEARCH_AREA_' . $id,
            default    => 'SEARCH_' . strtoupper($type) . '_' . $id,
        };
    }
}
