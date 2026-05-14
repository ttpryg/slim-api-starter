<?php

declare(strict_types=1);

namespace App\Database;

abstract class Seeder
{
    abstract public function run(): void;
}
