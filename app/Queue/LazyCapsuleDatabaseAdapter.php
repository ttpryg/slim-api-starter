<?php

declare(strict_types=1);

namespace App\Queue;

use Illuminate\Database\Capsule\Manager as Capsule;
use Ttpryg\Queue\Contracts\DatabaseAdapterInterface;
use Ttpryg\Queue\Database\PdoDatabaseAdapter;

class LazyCapsuleDatabaseAdapter implements DatabaseAdapterInterface
{
    private ?PdoDatabaseAdapter $adapter = null;

    public function __construct(
        private readonly Capsule $capsule
    ) {}

    private function getAdapter(): PdoDatabaseAdapter
    {
        if (! $this->adapter instanceof PdoDatabaseAdapter) {
            $pdo = $this->capsule->connection()->getPdo();
            $this->adapter = new PdoDatabaseAdapter($pdo);
        }

        return $this->adapter;
    }

    public function insert(string $table, array $data): string|int
    {
        return $this->getAdapter()->insert($table, $data);
    }

    public function fetchWaitingJob(string $table, string $queue, array $priorities = []): ?object
    {
        return $this->getAdapter()->fetchWaitingJob($table, $queue, $priorities);
    }

    public function update(string $table, array $data, array $where): bool
    {
        return $this->getAdapter()->update($table, $data, $where);
    }

    public function delete(string $table, array $where): int
    {
        return $this->getAdapter()->delete($table, $where);
    }

    public function fetchFailed(string $table, ?string $queue = null, ?int $id = null): array
    {
        return $this->getAdapter()->fetchFailed($table, $queue, $id);
    }
}
