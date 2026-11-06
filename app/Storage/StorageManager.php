<?php

declare(strict_types=1);

namespace App\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use RuntimeException;

class StorageManager
{
    /**
     * @var array<string, FilesystemOperator>
     */
    private array $disks = [];

    /**
     * @param  array<string, array<string, mixed>>  $config
     */
    public function __construct(
        private array $config,
        private readonly string $defaultDisk = 'local'
    ) {}

    public function disk(?string $name = null): FilesystemOperator
    {
        $name ??= $this->defaultDisk;

        if (isset($this->disks[$name])) {
            return $this->disks[$name];
        }

        if (! isset($this->config[$name])) {
            throw new RuntimeException("Storage disk [{$name}] is not configured.");
        }

        return $this->disks[$name] = $this->createDisk($this->config[$name]);
    }

    /**
     * @param  array<string, mixed>  $diskConfig
     */
    private function createDisk(array $diskConfig): FilesystemOperator
    {
        $driver = $diskConfig['driver'] ?? 'local';

        if ($driver === 'local') {
            $root = (string) ($diskConfig['root'] ?? __DIR__.'/../../storage/app');
            if (! is_dir($root)) {
                mkdir($root, 0755, true);
            }

            $adapter = new LocalFilesystemAdapter($root);

            return new Filesystem($adapter);
        }

        throw new RuntimeException("Storage driver [{$driver}] is not supported.");
    }
}
