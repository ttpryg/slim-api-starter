<?php

declare(strict_types=1);

namespace App\Test;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;

abstract class TestCase extends PHPUnitTestCase
{
    protected function getAppInstance(): App
    {
        return require __DIR__.'/../config/bootstrap.php';
    }

    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $cookies = [],
        array $serverParams = []
    ): Request {
        $factory = new ServerRequestFactory;
        $request = $factory->createServerRequest($method, $path, $serverParams);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request->withCookieParams($cookies);
    }
}
