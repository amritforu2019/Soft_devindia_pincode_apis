<?php

declare(strict_types=1);

/**
 * Environment configuration.
 * Override values via server environment variables or edit defaults below.
 */
return [
    'database' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => (int) (getenv('DB_PORT') ?: 3306),
        'name'     => getenv('DB_NAME') ?: 'dev_pincode',
        'user'     => getenv('DB_USER') ?: 'dev_pincode_u',
        'password' => getenv('DB_PASS') ?: 'ynDiMBHteDknR3Jh',
        'charset'  => 'utf8mb4',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => true,
        ],
    ],

    'redis' => [
        'host'     => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port'     => (int) (getenv('REDIS_PORT') ?: 6379),
        'password' => getenv('REDIS_PASSWORD') ?: '7691fbbb1b85f4c01',
        'database' => (int) (getenv('REDIS_DB') ?: 0),
        'timeout'  => 1.5,
        'ttl'      => 86400,
        'prefix'   => 'LOC:',
    ],

    'app' => [
        'debug'            => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'timezone'         => 'Asia/Kolkata',
        'cors_origins'     => '*',
        'cors_methods'     => 'GET, OPTIONS',
        'cors_headers'     => 'Content-Type, Authorization, X-Requested-With',
        'jwt_enabled'      => filter_var(getenv('JWT_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'jwt_secret'       => getenv('JWT_SECRET') ?: '',
        'jwt_header'       => 'Authorization',
        'jwt_algorithm'    => 'HS256',
        'search_max_limit' => 50,
    ],

    'log' => [
        'path'    => __DIR__ . '/../logs/api_errors.log',
        'enabled' => true,
    ],
];
