<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Traits\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class JwtAuthMiddleware implements MiddlewareInterface
{
    use ResponseTrait;

    public function __construct(
        private readonly array $settings
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            $response = new \Slim\Psr7\Response();
            return $this->error($response, 'Missing or malformed Authorization header', 401);
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key($this->settings['secret'], $this->settings['algorithm']));
            $request = $request->withAttribute('jwt_payload', (array) $decoded);
        } catch (\Throwable $e) {
            $response = new \Slim\Psr7\Response();
            return $this->error($response, 'Invalid or expired token', 401);
        }

        return $handler->handle($request);
    }
}
