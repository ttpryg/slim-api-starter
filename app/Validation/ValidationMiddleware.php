<?php

declare(strict_types=1);

namespace App\Validation;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Symfony\Component\Validator\Constraint;
use Ttpryg\Validation\Validator;

final readonly class ValidationMiddleware implements MiddlewareInterface
{
    public const VALIDATED_ATTRIBUTE = 'validated';

    /**
     * @param  array<string, mixed>|Constraint  $rules
     */
    public function __construct(
        private array|Constraint $rules,
        private ?Validator $validator = null
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        $validator = $this->validator ?? new Validator;
        $body = (array) ($request->getParsedBody() ?? []);

        $validated = $validator->validate($body, $this->rules);

        $request = $request->withAttribute(self::VALIDATED_ATTRIBUTE, $validated);

        return $handler->handle($request);
    }
}
