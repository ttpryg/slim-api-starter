<?php

declare(strict_types=1);
use Monolog\Level;

return [
    'settings' => [
        'app_name' => 'Slim API Starter',
        'app_version' => 'v0.0.3',
        'displayErrorDetails' => (getenv('APP_ENV') === 'development' || filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN)),
        'logError' => true,
        'logErrorDetails' => true,
        'logger' => [
            'name' => 'slim',
            'path' => __DIR__.'/../storage/logs/app',
            'level' => Level::Debug,
            'maxFiles' => 14,
        ],
    ],

    'cors' => [
        'allowed_origins' => explode(',', (getenv('CORS_ALLOWED_ORIGINS') ?: '*')),
        'allowed_methods' => explode(',', (getenv('CORS_ALLOWED_METHODS') ?: 'GET,POST,PUT,PATCH,DELETE,OPTIONS')),
        'allowed_headers' => explode(',', (getenv('CORS_ALLOWED_HEADERS') ?: 'Content-Type,Authorization,X-Requested-With')),
        'exposed_headers' => explode(',', (getenv('CORS_EXPOSED_HEADERS') ?: '')),
        'max_age' => (int) (getenv('CORS_MAX_AGE') ?: 86400),
        'allow_credentials' => filter_var(getenv('CORS_ALLOW_CREDENTIALS'), FILTER_VALIDATE_BOOLEAN) ?: false,
    ],

    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production',
        'algorithm' => getenv('JWT_ALGORITHM') ?: 'HS256',
        'ttl' => (int) (getenv('JWT_TTL') ?: 3600),
        'refresh_ttl' => (int) (getenv('JWT_REFRESH_TTL') ?: 1209600),
        'issuer' => getenv('JWT_ISSUER') ?: 'slim-api-starter',
    ],

    'rate_limit' => [
        'max_requests' => (int) (getenv('RATE_LIMIT_MAX') ?: 60),
        'window' => (int) (getenv('RATE_LIMIT_WINDOW') ?: 60),
        'storage_path' => __DIR__.'/../storage/rate-limit',
    ],

    'database' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: 3306,
        'database' => getenv('DB_DATABASE') ?: 'slim_api',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        'collation' => getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci',
        'prefix' => getenv('DB_PREFIX') ?: '',
        'url' => getenv('DB_URL') ?: null,
    ],
];
