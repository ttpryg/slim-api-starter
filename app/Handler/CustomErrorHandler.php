<?php

declare(strict_types=1);

namespace App\Handler;

use App\Traits\ResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Handlers\ErrorHandler;

final class CustomErrorHandler extends ErrorHandler
{
    use ResponseTrait;

    protected function respond(): ResponseInterface
    {
        $statusCode = $this->statusCode;
        $message = $this->exception->getMessage() ?: 'Internal Server Error';

        $response = $this->responseFactory->createResponse($statusCode);

        $errors = null;
        if ($this->displayErrorDetails) {
            $errors = [
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'trace' => explode("\n", $this->exception->getTraceAsString()),
            ];
        }

        $response = $this->error($response, $message, $statusCode, $errors);

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $response = $response->withHeader('Allow', implode(', ', $this->exception->getAllowedMethods()));
        }

        return $response;
    }
}
