<?php

declare(strict_types=1);

namespace App\Test\Action;

use App\Test\TestCase;

class RegisterActionTest extends TestCase
{
    public function test_register_success(): void
    {
        $app = $this->getAppInstance();
        $serverRequest = $this->createRequest('POST', '/register')
            ->withHeader('Content-Type', 'application/json');
        $serverRequest->getBody()->write((string) json_encode([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]));

        $response = $app->handle($serverRequest);
        $this->assertEquals(201, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertTrue($payload['success']);
        $this->assertEquals('John Doe', $payload['data']['name']);
        $this->assertEquals('john@example.com', $payload['data']['email']);
    }

    public function test_register_validation_failure(): void
    {
        $app = $this->getAppInstance();
        $serverRequest = $this->createRequest('POST', '/register')
            ->withHeader('Content-Type', 'application/json');
        $serverRequest->getBody()->write((string) json_encode([
            'name' => 'Jo',
            'email' => 'invalid-email',
            'password' => 'short',
        ]));

        $response = $app->handle($serverRequest);
        $this->assertEquals(422, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertFalse($payload['success']);
        $this->assertArrayHasKey('errors', $payload);
        $this->assertArrayHasKey('name', $payload['errors']);
        $this->assertArrayHasKey('email', $payload['errors']);
        $this->assertArrayHasKey('password', $payload['errors']);
    }
}
