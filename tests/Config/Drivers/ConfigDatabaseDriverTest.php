<?php

declare(strict_types=1);

namespace App\Test\Config\Drivers;

use App\Config\Drivers\ConfigDatabaseDriver;
use PDO;
use PHPUnit\Framework\TestCase;

class ConfigDatabaseDriverTest extends TestCase
{
    private PDO $pdo;

    private ConfigDatabaseDriver $configDatabaseDriver;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->configDatabaseDriver = new ConfigDatabaseDriver($this->pdo);
    }

    public function test_does_not_auto_create_table(): void
    {
        $result = $this->pdo->query('SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'configs\'');
        $this->assertFalse($result->fetchColumn() !== false, 'Table should not be created automatically');
    }

    public function test_set_and_get(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $this->configDatabaseDriver->set('app.name', 'Test App');
        $result = $this->configDatabaseDriver->get('app.name');

        $this->assertEquals('Test App', $result);
    }

    public function test_get_returns_null_for_missing_key(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $result = $this->configDatabaseDriver->get('nonexistent.key');

        $this->assertNull($result);
    }

    public function test_set_updates_existing_key(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $this->configDatabaseDriver->set('app.name', 'Original');
        $this->configDatabaseDriver->set('app.name', 'Updated');
        $result = $this->configDatabaseDriver->get('app.name');

        $this->assertEquals('Updated', $result);
    }

    public function test_forget(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $this->configDatabaseDriver->set('app.name', 'Test');
        $this->configDatabaseDriver->forget('app.name');
        $result = $this->configDatabaseDriver->get('app.name');

        $this->assertNull($result);
    }

    public function test_all(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $this->configDatabaseDriver->set('app.name', 'Test App');
        $this->configDatabaseDriver->set('app.version', '1.0.0');
        $result = $this->configDatabaseDriver->all();

        $this->assertEquals([
            'app.name' => 'Test App',
            'app.version' => '1.0.0',
        ], $result);
    }

    public function test_all_returns_empty_array_when_no_data(): void
    {
        $this->pdo->exec('CREATE TABLE configs (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $result = $this->configDatabaseDriver->all();

        $this->assertEquals([], $result);
    }

    public function test_with_custom_table_name(): void
    {
        $configDatabaseDriver = new ConfigDatabaseDriver($this->pdo, 'app_settings');
        $this->pdo->exec('CREATE TABLE app_settings (key VARCHAR(255) PRIMARY KEY, value TEXT)');

        $configDatabaseDriver->set('app.name', 'Test');
        $result = $configDatabaseDriver->get('app.name');

        $this->assertEquals('Test', $result);
    }
}
