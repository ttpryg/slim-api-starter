<?php

declare(strict_types=1);

namespace App\Test\Middleware;

use App\Middleware\RequestIdMiddleware;
use App\Test\TestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class RequestIdMiddlewareTest extends TestCase
{
    public function test_it_generates_uuid_header_if_not_present(): void
    {
        $middleware = new RequestIdMiddleware;
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');

        $response = $middleware->process($request, new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $requestId = $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME);
                $response = (new ResponseFactory)->createResponse();
                $response->getBody()->write((string) $requestId);

                return $response;
            }
        });

        $this->assertTrue($response->hasHeader(RequestIdMiddleware::HEADER_NAME));
        $headerId = $response->getHeaderLine(RequestIdMiddleware::HEADER_NAME);
        $this->assertNotEmpty($headerId);
        $this->assertSame($headerId, (string) $response->getBody());
    }

    public function test_it_preserves_incoming_request_id(): void
    {
        $middleware = new RequestIdMiddleware;
        $customId = 'custom-request-id-12345';
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/')
            ->withHeader(RequestIdMiddleware::HEADER_NAME, $customId);

        $response = $middleware->process($request, new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return (new ResponseFactory)->createResponse();
            }
        });

        $this->assertSame($customId, $response->getHeaderLine(RequestIdMiddleware::HEADER_NAME));
    }

    public function test_it_adds_request_id_to_monolog_record(): void
    {
        $logger = new Logger('test');
        $testHandler = new TestHandler;
        $logger->pushHandler($testHandler);

        $middleware = new RequestIdMiddleware($logger);
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');

        $middleware->process($request, new class($logger) implements RequestHandlerInterface
        {
            public function __construct(private readonly Logger $logger) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->logger->info('Hello from inside request');

                return (new ResponseFactory)->createResponse();
            }
        });

        $records = $testHandler->getRecords();
        $this->assertCount(1, $records);
        $this->assertArrayHasKey('request_id', $records[0]->extra);
    }
}
