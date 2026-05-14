<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
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

// Initialize Eloquent Capsule globally (for commands, models, and web app)
$container->get(Capsule::class);

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

$errorMiddleware->setDefaultErrorHandler(function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();

    $status = 500;
    if ($exception instanceof HttpException) {
        $status = $exception->getCode();
    }

    $payload = [
        'success' => false,
        'message' => $exception->getMessage() ?: 'Internal Server Error',
    ];

    if ($displayErrorDetails) {
        $payload['errors'] = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => explode("\n", $exception->getTraceAsString()),
        ];
    }

    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_SLASHES)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($status);
});

return $app;
