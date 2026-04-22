<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        Capsule::class => function (ContainerInterface $c) {
            $capsule = new Capsule(new Container);

            $capsule->addConnection($c->get('database'));

            $capsule->setEventDispatcher(new Dispatcher(new Container));

            $capsule->bootEloquent();
            $capsule->setAsGlobal();

            return $capsule;
        },
    ]);
};
