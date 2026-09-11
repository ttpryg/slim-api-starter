<?php

declare(strict_types=1);

namespace App\Test\Validation;

use App\Exception\ValidationException;
use App\Test\TestCase;
use App\Validation\Validator;
use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email
    ) {}
}

class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator;
    }

    public function test_validate_passes_valid_data(): void
    {
        $input = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ];

        $rules = [
            'name' => 'required|min:3|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];

        $validated = $this->validator->validate($input, $rules);

        $this->assertEquals($input, $validated);
    }

    public function test_validate_throws_exception_on_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        $input = [
            'name' => 'Jo',
            'email' => 'not-an-email',
            'password' => '123',
        ];

        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];

        try {
            $this->validator->validate($input, $rules);
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('password', $errors);
            $this->assertArrayHasKey('name', $errors);
            throw $e;
        }
    }

    public function test_validate_with_object_attributes(): void
    {
        $userDto = new UserDto('valid@example.com');
        $result = $this->validator->validate($userDto);

        $this->assertSame($userDto, $result);
    }

    public function test_validate_with_object_attributes_throws_exception(): void
    {
        $this->expectException(ValidationException::class);

        $userDto = new UserDto('invalid-email');
        $this->validator->validate($userDto);
    }

    public function test_validate_with_direct_symfony_constraints(): void
    {
        $input = ['age' => 25];
        $rules = [
            'age' => [new Assert\NotBlank, new Assert\Type('integer'), new Assert\GreaterThanOrEqual(18)],
        ];

        $validated = $this->validator->validate($input, $rules);
        $this->assertEquals($input, $validated);
    }
}
