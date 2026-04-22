<?php

declare(strict_types=1);

namespace App\Test\Action;

use App\Test\TestCase;
use App\Traits\ResponseTrait;

class HomeActionTest extends TestCase
{
    use ResponseTrait;

    public function test_home_action_returns_hello_world(): void
    {
        $app = $this->getAppInstance();
        $request = $this->createRequest('GET', '/');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();

        $expectedResponse = $this->success(
            $app->getResponseFactory()->createResponse(),
            null,
            'Hello World!'
        );
        $expectedPayload = (string) $expectedResponse->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals($expectedPayload, $payload);
    }
}
