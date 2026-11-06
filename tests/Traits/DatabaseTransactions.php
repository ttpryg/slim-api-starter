<?php

declare(strict_types=1);

namespace App\Test\Traits;

use Illuminate\Database\Capsule\Manager as Capsule;

trait DatabaseTransactions
{
    protected function setUpDatabaseTransactions(): void
    {
        Capsule::connection()->beginTransaction();
    }

    protected function tearDownDatabaseTransactions(): void
    {
        if (Capsule::connection()->transactionLevel() > 0) {
            Capsule::connection()->rollBack();
        }
    }
}
