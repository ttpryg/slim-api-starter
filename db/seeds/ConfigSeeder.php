<?php

declare(strict_types=1);

use App\Config\Drivers\ConfigDatabaseDriver;
use App\Database\Seeder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Ttpryg\Config\ConfigRepository;

return new class extends Seeder
{
    public function run(): void
    {
        $pdo = Capsule::connection()->getPdo();
        $driver = new ConfigDatabaseDriver($pdo);
        $config = new ConfigRepository;

        $config->set('settings.app_name', 'Slim API Starter');
        $config->set('settings.app_version', 'v0.0.3');
        $config->set('settings.displayErrorDetails', getenv('APP_ENV') === 'development' || filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN));
        $config->set('settings.logError', true);
        $config->set('settings.logErrorDetails', true);
        $config->set('settings.logger.name', 'slim');
        $config->set('settings.logger.path', __DIR__.'/../../storage/logs/app');
        $config->set('settings.logger.level', 'debug');
        $config->set('settings.logger.maxFiles', 14);

        $config->set('cors.allowed_origins', explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: '*'));
        $config->set('cors.allowed_methods', explode(',', getenv('CORS_ALLOWED_METHODS') ?: 'GET,POST,PUT,PATCH,DELETE,OPTIONS'));
        $config->set('cors.allowed_headers', explode(',', getenv('CORS_ALLOWED_HEADERS') ?: 'Content-Type,Authorization,X-Requested-With'));
        $config->set('cors.exposed_headers', explode(',', getenv('CORS_EXPOSED_HEADERS') ?: ''));
        $config->set('cors.max_age', (int) (getenv('CORS_MAX_AGE') ?: 86400));
        $config->set('cors.allow_credentials', filter_var(getenv('CORS_ALLOW_CREDENTIALS'), FILTER_VALIDATE_BOOLEAN));

        $config->set('jwt.secret', getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production');
        $config->set('jwt.algorithm', getenv('JWT_ALGORITHM') ?: 'HS256');
        $config->set('jwt.ttl', (int) (getenv('JWT_TTL') ?: 3600));
        $config->set('jwt.refresh_ttl', (int) (getenv('JWT_REFRESH_TTL') ?: 1209600));
        $config->set('jwt.issuer', getenv('JWT_ISSUER') ?: 'slim-api-starter');

        $config->set('rate_limit.max_requests', (int) (getenv('RATE_LIMIT_MAX') ?: 60));
        $config->set('rate_limit.window', (int) (getenv('RATE_LIMIT_WINDOW') ?: 60));
        $config->set('rate_limit.storage_path', __DIR__.'/../../storage/rate-limit');

        $config->set('database.driver', getenv('DB_DRIVER') ?: 'mysql');
        $config->set('database.host', getenv('DB_HOST') ?: 'localhost');
        $config->set('database.port', getenv('DB_PORT') ?: 3306);
        $config->set('database.database', getenv('DB_DATABASE') ?: 'slim_api');
        $config->set('database.username', getenv('DB_USERNAME') ?: 'root');
        $config->set('database.password', getenv('DB_PASSWORD') ?: '');
        $config->set('database.charset', getenv('DB_CHARSET') ?: 'utf8mb4');
        $config->set('database.collation', getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci');
        $config->set('database.prefix', getenv('DB_PREFIX') ?: '');
        $config->set('database.url', getenv('DB_URL') ?: null);

        $config->saveToDatabase($driver);
    }
};
