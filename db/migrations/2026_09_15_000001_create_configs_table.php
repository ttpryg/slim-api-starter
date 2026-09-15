<?php

declare(strict_types=1);

use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('configs', function ($table) {
            $table->string('key')->primary();
            $table->longText('value')->nullable();
        });
    }

    public function down(): void
    {
        $this->schema->dropIfExists('configs');
    }
};
