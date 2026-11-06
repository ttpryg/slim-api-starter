<?php

declare(strict_types=1);

namespace App\Test\Traits;

use App\Database\Migrator;
use Illuminate\Database\Capsule\Manager as Capsule;

trait RefreshDatabase
{
    protected static bool $migrated = false;

    protected function setUpRefreshDatabase(): void
    {
        $this->ensureDatabaseConnection();

        if (! static::$migrated) {
            $migrator = new Migrator;
            $migrator->run(__DIR__.'/../../db/migrations');
            static::$migrated = true;
        }

        Capsule::connection()->beginTransaction();
    }

    protected function tearDownRefreshDatabase(): void
    {
        if (Capsule::connection()->transactionLevel() > 0) {
            Capsule::connection()->rollBack();
        }
    }

    private function ensureDatabaseConnection(): void
    {
        // Touch Capsule connection if not initialized yet
        Capsule::connection();
    }
}
