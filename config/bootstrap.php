<?php

declare(strict_types=1);

use App\Handler\CustomErrorHandler;
use App\Middleware\CorsMiddleware;
use App\Middleware\RateLimitMiddleware;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use Ttpryg\Config\ConfigRepository;

require __DIR__.'/../vendor/autoload.php';

// Load .env file
if (file_exists(__DIR__.'/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();
}

$containerBuilder = new ContainerBuilder;

// Set up bootstrap settings (database credentials only)
$settings = require __DIR__.'/../config/settings.php';
$containerBuilder->addDefinitions($settings);

// Set up dependencies
$dependencies = require __DIR__.'/../config/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Get config from container
$config = $container->get(ConfigRepository::class);

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register middleware
$app->add(new RateLimitMiddleware($config->get('rate_limit')));
$app->add(new CorsMiddleware($config->get('cors')));
$app->addBodyParsingMiddleware(); // Parse json, form data and xml

// Register routes
$routes = require __DIR__.'/../config/routes.php';
$routes($app);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(
    $config->get('settings.displayErrorDetails'),
    $config->get('settings.logError'),
    $config->get('settings.logErrorDetails'),
    $container->get(LoggerInterface::class)
);

$errorMiddleware->setDefaultErrorHandler(
    new CustomErrorHandler(
        $app->getCallableResolver(),
        $app->getResponseFactory(),
        $container->get(LoggerInterface::class)
    )
);

return $app;
