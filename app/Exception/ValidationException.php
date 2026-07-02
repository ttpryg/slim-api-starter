<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    private array $errors;

    public function __construct(array $errors, string $message = 'Validation failed')
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
