<?php

declare(strict_types=1);

use App\Config\Drivers\ConfigDatabaseDriver;
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
use Ttpryg\Config\ConfigManager;
use Ttpryg\Config\ConfigRepository;

return function (ContainerBuilder $containerBuilder): void {
    $containerBuilder->addDefinitions([
        SymfonyValidatorInterface::class => fn (): SymfonyValidatorInterface => Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator(),

        Validator::class => fn (ContainerInterface $container): Validator => new Validator($container->get(SymfonyValidatorInterface::class)),

        ConfigRepository::class => function (ContainerInterface $container): ConfigRepository {
            $capsule = $container->get(Capsule::class);
            $pdo = $capsule->connection()->getPdo();
            $driver = new ConfigDatabaseDriver($pdo);

            if (Capsule::schema()->hasTable('configs')) {
                return ConfigManager::createFromDatabase($driver);
            }

            $config = new ConfigRepository;
            $config->set('settings.app_name', getenv('APP_NAME') ?: 'Slim API Starter');
            $config->set('settings.app_version', getenv('APP_VERSION') ?: 'v0.0.1');
            $config->set('settings.displayErrorDetails', getenv('APP_ENV') === 'development' || filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN));
            $config->set('settings.logError', true);
            $config->set('settings.logErrorDetails', true);
            $config->set('settings.logger.name', 'slim');
            $config->set('settings.logger.path', __DIR__.'/../storage/logs/app');
            $config->set('settings.logger.level', 'debug');
            $config->set('settings.logger.maxFiles', 14);
            $config->set('cors.allowed_origins', explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: '*'));
            $config->set('cors.allowed_methods', explode(',', getenv('CORS_ALLOWED_METHODS') ?: 'GET,POST,PUT,PATCH,DELETE,OPTIONS'));
            $config->set('cors.allowed_headers', explode(',', getenv('CORS_ALLOWED_HEADERS') ?: 'Content-Type,Authorization,X-Requested-With'));
            $config->set('cors.exposed_headers', explode(',', getenv('CORS_EXPOSED_HEADERS') ?: ''));
            $config->set('cors.max_age', (int) (getenv('CORS_MAX_AGE') ?: 86400));
            $config->set('cors.allow_credentials', filter_var(getenv('CORS_ALLOW_CREDENTIALS'), FILTER_VALIDATE_BOOLEAN));
            $config->set('jwt.secret', getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production');
            $config->set('jwt.algorithm', getenv('JWT_ALGORITHM') ?: 'HS256');
            $config->set('jwt.ttl', (int) (getenv('JWT_TTL') ?: 3600));
            $config->set('jwt.refresh_ttl', (int) (getenv('JWT_REFRESH_TTL') ?: 1209600));
            $config->set('jwt.issuer', getenv('JWT_ISSUER') ?: 'slim-api-starter');
            $config->set('rate_limit.max_requests', (int) (getenv('RATE_LIMIT_MAX') ?: 60));
            $config->set('rate_limit.window', (int) (getenv('RATE_LIMIT_WINDOW') ?: 60));
            $config->set('rate_limit.storage_path', __DIR__.'/../storage/rate-limit');
            $config->set('database.driver', getenv('DB_DRIVER') ?: 'mysql');
            $config->set('database.host', getenv('DB_HOST') ?: 'localhost');
            $config->set('database.port', getenv('DB_PORT') ?: 3306);
            $config->set('database.database', getenv('DB_DATABASE') ?: 'slim_api');
            $config->set('database.username', getenv('DB_USERNAME') ?: 'root');
            $config->set('database.password', getenv('DB_PASSWORD') ?: '');
            $config->set('database.charset', getenv('DB_CHARSET') ?: 'utf8mb4');
            $config->set('database.collation', getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci');
            $config->set('database.prefix', getenv('DB_PREFIX') ?: '');
            $config->set('database.url', getenv('DB_URL') ?: null);

            return $config;
        },

        'config' => function (ContainerInterface $container): ConfigRepository {
            return $container->get(ConfigRepository::class);
        },

        'jwt' => function (ContainerInterface $container): array {
            return $container->get(ConfigRepository::class)->get('jwt');
        },

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
    ]);
};
