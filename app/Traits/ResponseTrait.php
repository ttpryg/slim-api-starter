<?php

declare(strict_types=1);

namespace App\Traits;

use Psr\Http\Message\ResponseInterface as Response;

trait ResponseTrait
{
    /**
     * Send a JSON response
     */
    protected function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    /**
     * Send a success JSON response
     */
    protected function success(
        Response $response,
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ): Response {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return $this->json($response, $payload, $status);
    }

    /**
     * Send an error JSON response
     */
    protected function error(
        Response $response,
        string $message = 'Error',
        int $status = 400,
        mixed $errors = null
    ): Response {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return $this->json($response, $payload, $status);
    }
}
