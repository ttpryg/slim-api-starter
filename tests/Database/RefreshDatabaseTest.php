<?php

declare(strict_types=1);

namespace App\Test\Database;

use App\Test\TestCase;
use App\Test\Traits\RefreshDatabase;
use Illuminate\Database\Capsule\Manager as Capsule;

class RefreshDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_are_executed(): void
    {
        $this->assertTrue(Capsule::schema()->hasTable('configs'));
        $this->assertTrue(Capsule::schema()->hasTable('migrations'));
    }

    public function test_data_is_isolated_in_transaction(): void
    {
        Capsule::table('configs')->insert([
            'key' => 'test.key',
            'value' => 'test-value',
        ]);

        $this->assertSame(1, Capsule::table('configs')->where('key', 'test.key')->count());
    }

    public function test_previous_transaction_was_rolled_back(): void
    {
        // Must be rolled back from the previous test
        $this->assertSame(0, Capsule::table('configs')->where('key', 'test.key')->count());
    }
}
