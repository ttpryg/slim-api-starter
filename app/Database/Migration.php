<?php

declare(strict_types=1);

namespace App\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Builder;

abstract class Migration
{
    protected Capsule $capsule;

    protected Builder $schema;

    public function setCapsule(Capsule $capsule): void
    {
        $this->capsule = $capsule;
        $this->schema = $capsule->schema();
    }

    abstract public function up(): void;

    abstract public function down(): void;
}
