<?php

declare(strict_types=1);

namespace App\Middleware;

use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

final readonly class RequestIdMiddleware implements MiddlewareInterface
{
    public const HEADER_NAME = 'X-Request-Id';

    public const ATTRIBUTE_NAME = 'request_id';

    public function __construct(
        private ?LoggerInterface $logger = null
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        $headerValue = $request->getHeaderLine(self::HEADER_NAME);
        $requestId = $headerValue !== '' ? $headerValue : $this->generateUuid();

        // Push request_id to Monolog extra processor if logger is a Monolog instance
        $resetLogger = null;
        if ($this->logger instanceof Logger) {
            $resetLogger = $this->logger->pushProcessor(fn (LogRecord $record): LogRecord => $record->with(extra: array_merge($record->extra, ['request_id' => $requestId])));
        }

        try {
            $request = $request->withAttribute(self::ATTRIBUTE_NAME, $requestId);
            $response = $handler->handle($request);

            return $response->withHeader(self::HEADER_NAME, $requestId);
        } finally {
            if ($this->logger instanceof Logger && $resetLogger instanceof Logger) {
                $this->logger->popProcessor();
            }
        }
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
