<?php

declare(strict_types=1);

final class RedisClient
{
    private static ?Redis $instance = null;

    private function __construct()
    {
    }

    public static function getConnection(): Redis
    {
        if (self::$instance instanceof Redis) {
            return self::$instance;
        }

        if (!extension_loaded('redis')) {
            throw new RuntimeException('Redis extension is not installed.');
        }

        $config = require __DIR__ . '/env.php';
        $redisConfig = $config['redis'];

        $redis = new Redis();

        try {
            $connected = $redis->connect(
                $redisConfig['host'],
                $redisConfig['port'],
                $redisConfig['timeout']
            );

            if (!$connected) {
                throw new RuntimeException('Unable to connect to Redis server.');
            }

            if ($redisConfig['password'] !== '') {
                if (!$redis->auth($redisConfig['password'])) {
                    throw new RuntimeException('Redis authentication failed.');
                }
            }

            if ($redisConfig['database'] > 0) {
                $redis->select($redisConfig['database']);
            }

            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
        } catch (Throwable $e) {
            Logger::error('Redis connection failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Cache service unavailable.');
        }

        self::$instance = $redis;

        return self::$instance;
    }

    public static function disconnect(): void
    {
        if (self::$instance instanceof Redis) {
            self::$instance->close();
            self::$instance = null;
        }
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new RuntimeException('Cannot unserialize singleton.');
    }
}
