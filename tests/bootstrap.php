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
