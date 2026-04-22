<?php

declare(strict_types=1);

namespace App\Test\Action;

use App\Test\TestCase;

class HomeActionTest extends TestCase
{
    public function test_home_action_returns_hello_world(): void
    {
        $app = $this->getAppInstance();
        $request = $this->createRequest('GET', '/');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $expectedPayload = json_encode(['message' => 'Hello World!']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals($expectedPayload, $payload);
    }
}
