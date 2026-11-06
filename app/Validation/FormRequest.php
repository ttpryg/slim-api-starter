<?php

declare(strict_types=1);

namespace App\Validation;

use ArrayAccess;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Symfony\Component\Validator\Constraint;
use Ttpryg\Validation\Validator;

/**
 * @implements ArrayAccess<string, mixed>
 */
abstract class FormRequest implements ArrayAccess, FormRequestInterface, MiddlewareInterface
{
    public const ATTRIBUTE_NAME = 'form_request';

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @var array<string, mixed>
     */
    protected array $validatedData = [];

    public function __construct(
        protected ?Validator $validator = null
    ) {}

    /**
     * Define validation rules.
     *
     * @return array<string, mixed>|Constraint
     */
    abstract public function rules(): array|Constraint;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Middleware invocation handler.
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $this->validator ??= new Validator;
        $this->data = (array) ($request->getParsedBody() ?? []);

        $validated = $this->validator->validate($this->data, $this->rules());
        $this->validatedData = is_array($validated) ? $validated : [];

        $request = $request
            ->withAttribute(self::ATTRIBUTE_NAME, $this)
            ->withAttribute(ValidationMiddleware::VALIDATED_ATTRIBUTE, $this->validatedData);

        return $handler->handle($request);
    }

    /**
     * Return all validated input.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        return $this->validatedData;
    }

    /**
     * Return a specific validated input value or fallback.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->validatedData[$key] ?? $default;
    }

    /**
     * Return all raw input data.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->validatedData[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->validatedData[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->validatedData[] = $value;
        } else {
            $this->validatedData[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->validatedData[$offset]);
    }
}
