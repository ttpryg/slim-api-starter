<?php

declare(strict_types=1);

use App\Validation\Validator;
use DI\ContainerBuilder;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([
        SymfonyValidatorInterface::class => fn (): SymfonyValidatorInterface => Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator(),

        Validator::class => fn (ContainerInterface $container): Validator => new Validator($container->get(SymfonyValidatorInterface::class)),

        LoggerInterface::class => function (ContainerInterface $container): Logger {
            $settings = $container->get('settings')['logger'];

            $logger = new Logger($settings['name']);

            $processor = new UidProcessor;
            $logger->pushProcessor($processor);

            $handler = new RotatingFileHandler(
                $settings['path'],
                $settings['maxFiles'],
                $settings['level']
            );
            $logger->pushHandler($handler);

            return $logger;
        },

        Manager::class => function (): Manager {
            $manager = new Manager;
            $manager->setSerializer(new ArraySerializer);

            return $manager;
        },

        Capsule::class => function (ContainerInterface $container): Capsule {
            $capsule = new Capsule(new Container);

            $capsule->addConnection($container->get('database'));

            $capsule->setEventDispatcher(new Dispatcher(new Container));

            $capsule->bootEloquent();
            $capsule->setAsGlobal();

            return $capsule;
        },
    ]);
};
