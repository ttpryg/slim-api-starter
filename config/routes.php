<?php

declare(strict_types=1);

use App\Action\HomeAction;
use App\Middleware\JwtAuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', HomeAction::class);

    // Protected API routes (require JWT token)
    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('/me', function ($request, $response) {
            $payload = $request->getAttribute('jwt_payload');
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Authenticated',
                'data' => $payload,
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        });
    })->add(new JwtAuthMiddleware($app->getContainer()->get('jwt')));
};
