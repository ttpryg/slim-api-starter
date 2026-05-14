<?php

declare(strict_types=1);

namespace App\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use RuntimeException;

class Migrator
{
    private Capsule $capsule;

    private string $table = 'migrations';

    public function __construct(Capsule $capsule)
    {
        $this->capsule = $capsule;
    }

    public function run(string $path): array
    {
        $this->ensureRepository();

        $ran = $this->getRan();
        $files = $this->getMigrationFiles($path);

        $pending = array_diff($files, $ran);

        if (empty($pending)) {
            return [];
        }

        $batch = $this->getNextBatchNumber();
        $results = [];

        foreach ($pending as $file) {
            $migration = $this->resolve($path, $file);
            $migration->setCapsule($this->capsule);
            $migration->up();
            $this->log($file, $batch);
            $results[] = $file;
        }

        return $results;
    }

    public function rollback(string $path, int $steps = 1): array
    {
        $this->ensureRepository();

        $migrations = $this->capsule->table($this->table)
            ->where('batch', function ($query) {
                $query->from($this->table)->selectRaw('MAX(batch)');
            })
            ->orderBy('migration', 'desc')
            ->get();

        if ($migrations->isEmpty()) {
            return [];
        }

        $results = [];

        foreach ($migrations as $m) {
            if ($steps <= 0) {
                break;
            }

            $migration = $this->resolve($path, $m->migration);
            $migration->setCapsule($this->capsule);
            $migration->down();

            $this->capsule->table($this->table)
                ->where('migration', $m->migration)
                ->delete();

            $results[] = $m->migration;
            $steps--;
        }

        return $results;
    }

    public function reset(string $path): array
    {
        $this->ensureRepository();

        $migrations = $this->capsule->table($this->table)
            ->orderBy('batch', 'desc')
            ->orderBy('migration', 'desc')
            ->get();

        if ($migrations->isEmpty()) {
            return [];
        }

        $results = [];

        foreach ($migrations as $m) {
            $migration = $this->resolve($path, $m->migration);
            $migration->setCapsule($this->capsule);
            $migration->down();

            $this->capsule->table($this->table)
                ->where('migration', $m->migration)
                ->delete();

            $results[] = $m->migration;
        }

        return $results;
    }

    private function ensureRepository(): void
    {
        if ($this->capsule->schema()->hasTable($this->table)) {
            return;
        }

        $this->capsule->schema()->create($this->table, function ($table) {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    private function getRan(): array
    {
        return $this->capsule->table($this->table)
            ->orderBy('migration')
            ->pluck('migration')
            ->toArray();
    }

    private function getMigrationFiles(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $files = scandir($path);
        $files = array_diff($files, ['.', '..', '.gitkeep']);
        sort($files);

        return array_map(fn ($file) => pathinfo($file, PATHINFO_FILENAME), $files);
    }

    private function resolve(string $path, string $file): Migration
    {
        $filePath = $path.'/'.$file.'.php';

        if (! file_exists($filePath)) {
            throw new RuntimeException("Migration file not found: {$filePath}");
        }

        $migration = require $filePath;

        if (! $migration instanceof Migration) {
            throw new RuntimeException('Migration file must return an instance of '.Migration::class);
        }

        return $migration;
    }

    private function log(string $file, int $batch): void
    {
        $this->capsule->table($this->table)->insert([
            'migration' => $file,
            'batch' => $batch,
        ]);
    }

    private function getNextBatchNumber(): int
    {
        $max = $this->capsule->table($this->table)->max('batch');

        return ($max ?? 0) + 1;
    }
}
