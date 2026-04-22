<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        Capsule::class => function () {
            $dbDriver = getenv('DB_DRIVER') ?: 'mysql';
            $dbHost = getenv('DB_HOST') ?: 'localhost';
            $dbPort = getenv('DB_PORT') ?: 3306;
            $dbDatabase = getenv('DB_DATABASE') ?: 'slim_api';
            $dbUsername = getenv('DB_USERNAME') ?: 'root';
            $dbPassword = getenv('DB_PASSWORD') ?: '';
            $dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';
            $dbCollation = getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci';
            $dbPrefix = getenv('DB_PREFIX') ?: '';

            $config = [
                'driver'    => $dbDriver,
                'host'      => $dbHost,
                'port'      => $dbPort,
                'database'  => $dbDatabase,
                'username'  => $dbUsername,
                'password'  => $dbPassword,
                'charset'   => $dbCharset,
                'collation'  => $dbCollation,
                'prefix'     => $dbPrefix,
            ];

            $capsule = new Capsule(new Container());

            $capsule->addConnection($config);

            $capsule->setEventDispatcher(new Dispatcher(new Container()));

            $capsule->setAsGlobal();

            $capsule->bootEloquent();

            return $capsule;
        },
    ]);
};