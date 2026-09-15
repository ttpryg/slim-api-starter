<?php

declare(strict_types=1);

namespace App\Test\Config;

use App\Test\TestCase;
use Ttpryg\Config\ConfigRepository;

class ConfigIntegrationTest extends TestCase
{
    public function test_config_is_registered_in_container(): void
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();

        $config = $container->get(ConfigRepository::class);

        $this->assertInstanceOf(ConfigRepository::class, $config);
    }

    public function test_config_key_is_registered_in_container(): void
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();

        $config = $container->get('config');

        $this->assertInstanceOf(ConfigRepository::class, $config);
    }

    public function test_settings_config_is_loaded(): void
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();
        $config = $container->get(ConfigRepository::class);

        $this->assertNotNull($config->get('settings.app_name'));
    }

    public function test_cors_config_is_loaded(): void
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();
        $config = $container->get(ConfigRepository::class);

        $this->assertNotNull($config->get('cors.allowed_origins'));
    }

    public function test_jwt_config_is_loaded(): void
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();
        $config = $container->get(ConfigRepository::class);

        $this->assertNotNull($config->get('jwt.secret'));
    }

    public function test_database_config_is_loaded(): void
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();
        $config = $container->get(ConfigRepository::class);

        $this->assertNotNull($config->get('database.driver'));
    }
}
