<?php

declare(strict_types=1);

namespace App\Validation;

use App\Exception\ValidationException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final readonly class Validator
{
    private SymfonyValidatorInterface $symfonyValidator;

    public function __construct(?SymfonyValidatorInterface $symfonyValidator = null)
    {
        $this->symfonyValidator = $symfonyValidator ?? Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    /**
     * Validate array data or object against constraints or rule definitions.
     *
     * @param  array<string, mixed>|object  $data
     * @param  array<string, mixed>|Constraint  $rules
     * @return array<string, mixed>|object
     *
     * @throws ValidationException
     */
    public function validate(array|object $data, array|Constraint $rules = []): array|object
    {
        if (is_object($data)) {
            $constraint = $rules instanceof Constraint ? $rules : null;
            $violations = $this->symfonyValidator->validate($data, $constraint);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $property = trim($violation->getPropertyPath(), '[]');
                    $errors[$property][] = (string) $violation->getMessage();
                }

                throw new ValidationException($errors);
            }

            return $data;
        }

        if ($rules instanceof Constraint) {
            $constraint = $rules;
        } else {
            $fields = [];
            foreach ($rules as $field => $fieldRules) {
                $fields[$field] = $this->parseFieldRules($fieldRules);
            }

            $constraint = new Assert\Collection([
                'fields' => $fields,
                'allowExtraFields' => true,
            ]);
        }

        $violations = $this->symfonyValidator->validate($data, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $property = trim($violation->getPropertyPath(), '[]');
                $errors[$property][] = (string) $violation->getMessage();
            }

            throw new ValidationException($errors);
        }

        if (is_array($rules)) {
            return array_intersect_key($data, $rules);
        }

        return $data;
    }

    /**
     * Alias for validate method.
     *
     * @param  array<string, mixed>|object  $data
     * @param  array<string, mixed>|Constraint  $rules
     * @return array<string, mixed>|object
     */
    public function validated(array|object $data, array|Constraint $rules = []): array|object
    {
        return $this->validate($data, $rules);
    }

    private function parseFieldRules(mixed $fieldRules): Constraint
    {
        if ($fieldRules instanceof Constraint) {
            return $fieldRules;
        }

        $constraints = [];
        $isRequired = false;

        $rulesList = is_string($fieldRules) ? explode('|', $fieldRules) : (array) $fieldRules;

        foreach ($rulesList as $ruleList) {
            if ($ruleList instanceof Constraint) {
                if ($ruleList instanceof Assert\NotBlank) {
                    $isRequired = true;
                }
                $constraints[] = $ruleList;

                continue;
            }

            if (! is_string($ruleList)) {
                continue;
            }

            $parts = explode(':', $ruleList, 2);
            $ruleName = strtolower(trim($parts[0]));
            $param = $parts[1] ?? null;

            switch ($ruleName) {
                case 'required':
                case 'not_blank':
                    $isRequired = true;
                    $constraints[] = new Assert\NotBlank;
                    break;
                case 'email':
                    $constraints[] = new Assert\Email;
                    break;
                case 'min':
                    if ($param !== null) {
                        $constraints[] = new Assert\Length(min: (int) $param);
                    }
                    break;
                case 'max':
                    if ($param !== null) {
                        $constraints[] = new Assert\Length(max: (int) $param);
                    }
                    break;
                case 'length':
                    if ($param !== null) {
                        $constraints[] = new Assert\Length(exactly: (int) $param);
                    }
                    break;
                case 'string':
                case 'type:string':
                    $constraints[] = new Assert\Type('string');
                    break;
                case 'int':
                case 'integer':
                case 'type:int':
                case 'type:integer':
                    $constraints[] = new Assert\Type('integer');
                    break;
                case 'bool':
                case 'boolean':
                case 'type:bool':
                case 'type:boolean':
                    $constraints[] = new Assert\Type('boolean');
                    break;
                case 'array':
                case 'type:array':
                    $constraints[] = new Assert\Type('array');
                    break;
                case 'numeric':
                    $constraints[] = new Assert\Type('numeric');
                    break;
                case 'url':
                    $constraints[] = new Assert\Url;
                    break;
                case 'uuid':
                    $constraints[] = new Assert\Uuid;
                    break;
                case 'json':
                    $constraints[] = new Assert\Json;
                    break;
                case 'regex':
                    if ($param !== null) {
                        $constraints[] = new Assert\Regex(pattern: $param);
                    }
                    break;
                case 'in':
                    if ($param !== null) {
                        $choices = explode(',', $param);
                        $constraints[] = new Assert\Choice(choices: $choices);
                    }
                    break;
            }
        }

        return $isRequired
            ? new Assert\Required($constraints)
            : new Assert\Optional($constraints);
    }
}
