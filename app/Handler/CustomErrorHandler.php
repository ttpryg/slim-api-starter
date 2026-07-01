<?php

declare(strict_types=1);

namespace App\Handler;

use App\Exception\ValidationException;
use App\Traits\ResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Handlers\ErrorHandler;

final class CustomErrorHandler extends ErrorHandler
{
    use ResponseTrait;

    protected function respond(): ResponseInterface
    {
        $statusCode = $this->statusCode;

        if ($this->exception instanceof ValidationException) {
            $response = $this->responseFactory->createResponse(422);

            return $this->error($response, $this->exception->getMessage(), 422, $this->exception->getErrors());
        }

        $message = $this->exception instanceof HttpException || $this->displayErrorDetails
            ? ($this->exception->getMessage() ?: 'Internal Server Error')
            : 'Internal Server Error';

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
