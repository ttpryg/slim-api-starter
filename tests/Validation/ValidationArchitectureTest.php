<?php

declare(strict_types=1);

namespace App\Test\Validation;

use App\Test\TestCase;
use App\Validation\FormRequest;
use App\Validation\ValidationMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Ttpryg\Validation\Exception\ValidationException;

class SampleRegisterFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|min:4',
            'email' => 'required|email',
        ];
    }
}

class ValidationArchitectureTest extends TestCase
{
    public function test_validation_middleware_injects_validated_attribute(): void
    {
        $middleware = new ValidationMiddleware([
            'title' => 'required|min:3',
        ]);

        $request = (new ServerRequestFactory)->createServerRequest('POST', '/')
            ->withParsedBody(['title' => 'Slim 4 Starter', 'extra' => 'ignored']);

        $response = $middleware->process($request, new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $validated = $request->getAttribute(ValidationMiddleware::VALIDATED_ATTRIBUTE);
                $response = (new ResponseFactory)->createResponse();
                $response->getBody()->write((string) json_encode($validated));

                return $response;
            }
        });

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame(['title' => 'Slim 4 Starter'], $payload);
    }

    public function test_validation_middleware_throws_on_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        $middleware = new ValidationMiddleware([
            'title' => 'required|min:5',
        ]);

        $request = (new ServerRequestFactory)->createServerRequest('POST', '/')
            ->withParsedBody(['title' => 'Hi']);

        $middleware->process($request, new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return (new ResponseFactory)->createResponse();
            }
        });
    }

    public function test_form_request_middleware_and_accessors(): void
    {
        $formRequest = new SampleRegisterFormRequest;
        $request = (new ServerRequestFactory)->createServerRequest('POST', '/register')
            ->withParsedBody([
                'username' => 'alice',
                'email' => 'alice@example.com',
                'extra_field' => 'should_be_ignored',
            ]);

        $response = $formRequest->process($request, new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                /** @var SampleRegisterFormRequest $form */
                $form = $request->getAttribute(FormRequest::ATTRIBUTE_NAME);
                $validated = $request->getAttribute(ValidationMiddleware::VALIDATED_ATTRIBUTE);

                $response = (new ResponseFactory)->createResponse();
                $response->getBody()->write((string) json_encode([
                    'all' => $form->all(),
                    'validated' => $form->validated(),
                    'email' => $form->get('email'),
                    'array_access' => $form['username'],
                    'attr_validated' => $validated,
                ]));

                return $response;
            }
        });

        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('alice', $body['array_access']);
        $this->assertSame('alice@example.com', $body['email']);
        $this->assertEquals(['username' => 'alice', 'email' => 'alice@example.com'], $body['validated']);
        $this->assertArrayHasKey('extra_field', $body['all']);
    }
}
