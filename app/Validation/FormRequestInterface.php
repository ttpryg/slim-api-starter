<?php

declare(strict_types=1);

namespace App\Validation;

use Symfony\Component\Validator\Constraint;

interface FormRequestInterface
{
    /**
     * Define validation rules using Laravel-style string rules or Symfony Constraints.
     *
     * @return array<string, mixed>|Constraint
     */
    public function rules(): array|Constraint;
}
