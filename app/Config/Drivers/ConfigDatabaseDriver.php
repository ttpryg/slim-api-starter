<?php

declare(strict_types=1);

namespace App\Config\Drivers;

use PDO;
use Ttpryg\Config\Drivers\PdoDatabaseDriver;

class ConfigDatabaseDriver extends PdoDatabaseDriver
{
    public function __construct(
        PDO $pdo,
        string $table = 'configs',
        string $keyColumn = 'key',
        string $valueColumn = 'value'
    ) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->keyColumn = $keyColumn;
        $this->valueColumn = $valueColumn;
    }
}
