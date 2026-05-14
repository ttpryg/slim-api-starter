<?php

declare(strict_types=1);

namespace App\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

abstract class Seeder
{
    protected Capsule $capsule;

    public function setCapsule(Capsule $capsule): void
    {
        $this->capsule = $capsule;
    }

    abstract public function run(): void;
}
