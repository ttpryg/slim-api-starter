<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;

require __DIR__.'/../vendor/autoload.php';

// Load .env file
if (file_exists(__DIR__.'/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();
}

$containerBuilder = new ContainerBuilder;

// Set up settings
$settings = require __DIR__.'/../config/settings.php';
$containerBuilder->addDefinitions($settings);

// Set up dependencies
$dependencies = require __DIR__.'/../config/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register routes
$routes = require __DIR__.'/../config/routes.php';
$routes($app);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(
    $container->get('settings')['displayErrorDetails'],
    $container->get('settings')['logError'],
    $container->get('settings')['logErrorDetails'],
    $container->get(LoggerInterface::class)
);

return $app;
