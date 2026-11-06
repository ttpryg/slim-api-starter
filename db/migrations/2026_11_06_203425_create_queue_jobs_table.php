<?php

declare(strict_types=1);

use App\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->schema->hasTable('queue_jobs')) {
            $this->schema->create('queue_jobs', function (Blueprint $table): void {
                $table->id();
                $table->string('queue', 100)->default('default')->index();
                $table->string('job');
                $table->longText('payload');
                $table->unsignedTinyInteger('priority')->default(0)->index();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->string('status', 20)->default('waiting')->index();
                $table->timestamp('available_at')->nullable()->index();
                $table->timestamp('reserved_at')->nullable();
                $table->string('reserved_by', 255)->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->text('exception')->nullable();
                $table->text('trace')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        $this->schema->dropIfExists('queue_jobs');
    }
};
