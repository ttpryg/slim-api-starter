<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly array $settings
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new \Slim\Psr7\Response();
            return $this->addCorsHeaders($response);
        }

        $response = $handler->handle($request);
        return $this->addCorsHeaders($response);
    }

    private function addCorsHeaders(Response $response): Response
    {
        $origin = $this->settings['allowed_origins'];

        if (in_array('*', $origin, true)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        } else {
            $response = $response->withHeader('Access-Control-Allow-Origin', implode(', ', $origin));
        }

        $response = $response
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->settings['allowed_methods']))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->settings['allowed_headers']));

        if (!empty($this->settings['exposed_headers'])) {
            $response = $response->withHeader('Access-Control-Expose-Headers', implode(', ', $this->settings['exposed_headers']));
        }

        if ($this->settings['max_age'] > 0) {
            $response = $response->withHeader('Access-Control-Max-Age', (string) $this->settings['max_age']);
        }

        if ($this->settings['allow_credentials']) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
