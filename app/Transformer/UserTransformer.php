<?php

declare(strict_types=1);

namespace App\Transformer;

final class UserTransformer extends Transformer
{
    public function transform(array $user): array
    {
        return [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];
    }
}
