<?php

declare(strict_types=1);

namespace App\Action;

use App\Traits\ResponseTrait;
use Illuminate\Database\Capsule\Manager as Capsule;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HealthAction
{
    use ResponseTrait;

    private const STORAGE_DIRS = [
        'logs' => __DIR__ . '/../../storage/logs',
        'rate-limit' => __DIR__ . '/../../storage/rate-limit',
    ];

    public function __invoke(Request $request, Response $response): Response
    {
        $dbStatus = $this->checkDatabase();
        $storageStatus = $this->checkStorage();

        $allOk = $dbStatus === 'ok' && $storageStatus === 'ok';

        $data = [
            'status' => $allOk ? 'healthy' : 'degraded',
            'timestamp' => date('c'),
            'services' => [
                'database' => $dbStatus,
                'storage' => $storageStatus,
                'app' => 'ok',
            ],
        ];

        $statusCode = $allOk ? 200 : 503;

        return $this->json($response, [
            'success' => $allOk,
            'message' => $allOk ? 'Service is healthy' : 'Service is degraded',
            'data' => $data,
        ], $statusCode);
    }

    private function checkDatabase(): string
    {
        try {
            Capsule::connection()->select('SELECT 1');
            return 'ok';
        } catch (PDOException) {
            return 'unreachable';
        }
    }

    private function checkStorage(): string
    {
        foreach (self::STORAGE_DIRS as $name => $path) {
            if (!is_dir($path) || !is_writable($path)) {
                return 'unreachable';
            }
        }

        return 'ok';
    }
}
