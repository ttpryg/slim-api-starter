<?php

declare(strict_types=1);

use App\Cache\FileCache;
use App\Config\Drivers\ConfigDatabaseDriver;
use App\I18n\Translator;
use App\Queue\LazyCapsuleDatabaseAdapter;
use App\Storage\StorageManager;
use DI\ContainerBuilder;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use League\Flysystem\FilesystemOperator;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;
use Slim\Psr7\Factory\UriFactory;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;
use Ttpryg\Config\ConfigRepository;
use Ttpryg\Queue\Config\QueueConfig;
use Ttpryg\Queue\Contracts\QueueInterface;
use Ttpryg\Queue\QueueManager;
use Ttpryg\Validation\Validator;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([
        // PSR-17 HTTP Factories
        ResponseFactoryInterface::class => fn (): ResponseFactoryInterface => new ResponseFactory,
        ServerRequestFactoryInterface::class => fn (): ServerRequestFactoryInterface => new ServerRequestFactory,
        StreamFactoryInterface::class => fn (): StreamFactoryInterface => new StreamFactory,
        UriFactoryInterface::class => fn (): UriFactoryInterface => new UriFactory,
        UploadedFileFactoryInterface::class => fn (): UploadedFileFactoryInterface => new UploadedFileFactory,

        SymfonyValidatorInterface::class => fn (): SymfonyValidatorInterface => Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator(),

        Validator::class => fn (ContainerInterface $container): Validator => new Validator($container->get(SymfonyValidatorInterface::class)),

        Translator::class => fn (): Translator => new Translator('en'),

        ConfigRepository::class => function (ContainerInterface $container): ConfigRepository {
            $staticSettings = require __DIR__.'/settings.php';
            $config = new ConfigRepository($staticSettings);

            try {
                $capsule = $container->get(Capsule::class);
                if (Capsule::schema()->hasTable('configs')) {
                    $pdo = $capsule->connection()->getPdo();
                    $driver = new ConfigDatabaseDriver($pdo);
                    $config->loadDatabase($driver);
                }
            } catch (Throwable) {
                // Ignore DB connection issues or missing table during initial setup
            }

            return $config;
        },

        'config' => fn (ContainerInterface $container): ConfigRepository => $container->get(ConfigRepository::class),

        'jwt' => fn (ContainerInterface $container): array => $container->get(ConfigRepository::class)->get('jwt'),

        LoggerInterface::class => function (ContainerInterface $container): Logger {
            $config = $container->get(ConfigRepository::class);
            $loggerSettings = $config->get('settings.logger');

            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor;
            $logger->pushProcessor($processor);

            $handler = new RotatingFileHandler(
                $loggerSettings['path'],
                $loggerSettings['maxFiles'],
                $loggerSettings['level']
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

        StorageManager::class => fn (): StorageManager => new StorageManager([
            'local' => [
                'driver' => 'local',
                'root' => __DIR__.'/../storage/app',
            ],
            'public' => [
                'driver' => 'local',
                'root' => __DIR__.'/../storage/app/public',
            ],
        ]),

        FilesystemOperator::class => fn (ContainerInterface $container): FilesystemOperator => $container->get(StorageManager::class)->disk(),

        CacheInterface::class => fn (): CacheInterface => new FileCache(__DIR__.'/../storage/cache'),

        QueueManager::class => function (ContainerInterface $container): QueueManager {
            $capsule = $container->get(Capsule::class);
            $logger = $container->get(LoggerInterface::class);

            $config = QueueConfig::fromArray([
                'defaultHandler' => 'database',
                'database' => [
                    'table' => 'queue_jobs',
                ],
            ]);

            $dbAdapter = new LazyCapsuleDatabaseAdapter($capsule);

            return new QueueManager($config, $dbAdapter, $logger);
        },

        QueueInterface::class => fn (ContainerInterface $container): QueueInterface => $container->get(QueueManager::class)->init(),
    ]);
};
