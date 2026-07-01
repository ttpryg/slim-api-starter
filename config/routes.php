<?php

declare(strict_types=1);

use App\Action\HomeAction;
use App\Action\UserListAction;
use App\Middleware\JwtAuthMiddleware;
use App\Validation\Validator;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', HomeAction::class);

    // Example: Request Validation
    $app->post('/register', function ($request, $response) {
        $validator = new Validator;
        $validated = $validator->validated(
            (array) $request->getParsedBody(),
            [
                'name' => 'required|min:3|max:255',
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]
        );

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'data' => $validated,
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    });

    // Example: Resource Transformers & Pagination
    $app->get('/users', UserListAction::class);

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
