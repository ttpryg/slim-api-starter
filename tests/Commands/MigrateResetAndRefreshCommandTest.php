<?php

declare(strict_types=1);

namespace App\Test\Commands;

use App\Commands\MigrateRefreshCommand;
use App\Commands\MigrateResetCommand;
use App\Database\Migrator;
use App\Test\TestCase;
use App\Test\Traits\DatabaseTransactions;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MigrateResetAndRefreshCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure migrations are run first
        $migrator = new Migrator;
        $migrator->run(__DIR__.'/../../db/migrations');
    }

    public function test_migrate_reset_command(): void
    {
        $this->assertTrue(Capsule::schema()->hasTable('configs'));

        $application = new Application;
        $application->add(new MigrateResetCommand);

        $command = $application->find('migrate:reset');
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFalse(Capsule::schema()->hasTable('configs'));
    }

    public function test_migrate_refresh_command(): void
    {
        $application = new Application;
        $application->add(new MigrateRefreshCommand);

        $command = $application->find('migrate:refresh');
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertTrue(Capsule::schema()->hasTable('configs'));
    }

    public function test_migrate_refresh_with_seed_option(): void
    {
        $application = new Application;
        $application->add(new MigrateRefreshCommand);

        $command = $application->find('migrate:refresh');
        $tester = new CommandTester($command);
        $tester->execute(['--seed' => true]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertTrue(Capsule::schema()->hasTable('configs'));
        $this->assertGreaterThan(0, Capsule::table('configs')->count());
    }
}
