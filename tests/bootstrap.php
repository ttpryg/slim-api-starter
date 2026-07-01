<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

putenv('APP_ENV=testing');
putenv('APP_DEBUG=true');
putenv('DB_DRIVER=sqlite');
putenv('DB_DATABASE=:memory:');

$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = true;
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';

// CORS defaults for testing
putenv('CORS_ALLOWED_ORIGINS=*');
putenv('CORS_ALLOWED_METHODS=GET,POST,PUT,PATCH,DELETE,OPTIONS');
putenv('CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With');
putenv('CORS_ALLOW_CREDENTIALS=false');
$_ENV['CORS_ALLOWED_ORIGINS'] = '*';
$_ENV['CORS_ALLOWED_METHODS'] = 'GET,POST,PUT,PATCH,DELETE,OPTIONS';
$_ENV['CORS_ALLOWED_HEADERS'] = 'Content-Type,Authorization,X-Requested-With';
$_ENV['CORS_ALLOW_CREDENTIALS'] = 'false';

// JWT defaults for testing
putenv('JWT_SECRET=testing-secret-key');
putenv('JWT_ALGORITHM=HS256');
putenv('JWT_TTL=3600');
$_ENV['JWT_SECRET'] = 'testing-secret-key';
$_ENV['JWT_ALGORITHM'] = 'HS256';
$_ENV['JWT_TTL'] = '3600';

// Rate limit defaults for testing
putenv('RATE_LIMIT_MAX=120');
putenv('RATE_LIMIT_WINDOW=60');
$_ENV['RATE_LIMIT_MAX'] = '120';
$_ENV['RATE_LIMIT_WINDOW'] = '60';
