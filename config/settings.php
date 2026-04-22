<?php

declare(strict_types=1);

return [
    'settings' => [
        'displayErrorDetails' => (getenv('APP_ENV') === 'development' || filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN)),
        'logError'            => false,
        'logErrorDetails'     => false,
    ],

    'database' => [
        'driver'    => getenv('DB_DRIVER') ?: 'mysql',
        'host'      => getenv('DB_HOST') ?: 'localhost',
        'port'      => getenv('DB_PORT') ?: 3306,
        'database'  => getenv('DB_DATABASE') ?: 'slim_api',
        'username'  => getenv('DB_USERNAME') ?: 'root',
        'password'  => getenv('DB_PASSWORD') ?: '',
        'charset'   => getenv('DB_CHARSET') ?: 'utf8mb4',
        'collation' => getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci',
        'prefix'    => getenv('DB_PREFIX') ?: '',
        'url'       => getenv('DB_URL') ?: null,
    ],
];
