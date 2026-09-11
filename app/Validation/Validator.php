<?php

declare(strict_types=1);

namespace App\Validation;

use App\Exception\ValidationException;
use Rakit\Validation\Validator as RakitValidator;

final readonly class Validator
{
    private RakitValidator $rakitValidator;

    public function __construct(?RakitValidator $rakitValidator = null)
    {
        $this->rakitValidator = $rakitValidator ?? new RakitValidator;
    }

    public function validate(array $data, array $rules, array $messages = []): array
    {
        $validation = $this->rakitValidator->validate($data, $rules, $messages);

        if ($validation->fails()) {
            throw new ValidationException($validation->errors()->toArray());
        }

        return $validation->getValidatedData();
    }

    public function validated(array $data, array $rules, array $messages = []): array
    {
        return $this->validate($data, $rules, $messages);
    }
}
