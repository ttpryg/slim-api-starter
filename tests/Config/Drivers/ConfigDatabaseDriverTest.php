<?php

declare(strict_types=1);

namespace App\Test\Config\Drivers;

use App\Config\Drivers\ConfigDatabaseDriver;
use PDO;
use PHPUnit\Framework\TestCase;

class ConfigDatabaseDriverTest extends TestCase
{
    private PDO $pdo;

    private ConfigDatabaseDriver $driver;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->driver = new ConfigDatabaseDriver($this->pdo);
    }

    public function test_does_not_auto_create_table(): void
    {
        $result = $this->pdo->query('SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'configs\'');
        $this->assertFalse($result->fetchColumn() !== false, 'Table should not be created automatically');
    }

    public function test_set_and_get(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $this->driver->set('app.name', 'Test App');
        $result = $this->driver->get('app.name');

        $this->assertEquals('Test App', $result);
    }

    public function test_get_returns_null_for_missing_key(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $result = $this->driver->get('nonexistent.key');

        $this->assertNull($result);
    }

    public function test_set_updates_existing_key(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $this->driver->set('app.name', 'Original');
        $this->driver->set('app.name', 'Updated');
        $result = $this->driver->get('app.name');

        $this->assertEquals('Updated', $result);
    }

    public function test_forget(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $this->driver->set('app.name', 'Test');
        $this->driver->forget('app.name');
        $result = $this->driver->get('app.name');

        $this->assertNull($result);
    }

    public function test_all(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $this->driver->set('app.name', 'Test App');
        $this->driver->set('app.version', '1.0.0');
        $result = $this->driver->all();

        $this->assertEquals([
            'app.name' => 'Test App',
            'app.version' => '1.0.0',
        ], $result);
    }

    public function test_all_returns_empty_array_when_no_data(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $result = $this->driver->all();

        $this->assertEquals([], $result);
    }

    public function test_with_custom_table_name(): void
    {
        $driver = new ConfigDatabaseDriver($this->pdo, 'app_settings');
        $this->pdo->exec('CREATE TABLE app_settings (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $driver->set('app.name', 'Test');
        $result = $driver->get('app.name');

        $this->assertEquals('Test', $result);
    }
}
