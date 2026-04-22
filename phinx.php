<?php

$configDir = __DIR__;
$settings = require $configDir.'/config/settings.php';
$db = $settings['database'];

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_environment' => 'development',
        'production' => [
            'adapter' => $db['driver'],
            'host' => $db['host'],
            'name' => $db['database'],
            'user' => $db['username'],
            'pass' => $db['password'],
            'port' => $db['port'],
            'charset' => $db['charset'],
        ],
        'development' => [
            'adapter' => $db['driver'],
            'host' => $db['host'],
            'name' => $db['database'],
            'user' => $db['username'],
            'pass' => $db['password'],
            'port' => $db['port'],
            'charset' => $db['charset'],
        ],
        'testing' => [
            'adapter' => $db['driver'],
            'host' => $db['host'],
            'name' => $db['database'],
            'user' => $db['username'],
            'pass' => $db['password'],
            'port' => $db['port'],
            'charset' => $db['charset'],
        ],
    ],
    'version_order' => 'creation',
];
