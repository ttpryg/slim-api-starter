<?php

declare(strict_types=1);

namespace App\Action;

use App\Traits\TransformTrait;
use App\Transformer\UserTransformer;
use League\Fractal\Manager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class UserListAction
{
    use TransformTrait;

    public function __construct(Manager $manager)
    {
        $this->setFractalManager($manager);
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $users = [
            ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
            ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
        ];

        return $this->collection($response, $users, new UserTransformer, 'users');
    }
}
