<?php

declare(strict_types=1);

namespace App\Cache;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;

class FileCache implements CacheInterface
{
    public function __construct(
        private readonly string $directory
    ) {
        if (! is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);
        if (! file_exists($file)) {
            return $default;
        }

        $raw = file_get_contents($file);
        if ($raw === false) {
            return $default;
        }

        $data = unserialize($raw);
        if (! is_array($data) || ! array_key_exists('expires_at', $data) || ! array_key_exists('value', $data)) {
            $this->delete($key);

            return $default;
        }

        if (is_int($data['expires_at']) && time() > $data['expires_at']) {
            $this->delete($key);

            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $file = $this->getFilePath($key);
        $expiresAt = $this->calculateExpiration($ttl);

        $payload = serialize([
            'expires_at' => $expiresAt,
            'value' => $value,
        ]);

        return file_put_contents($file, $payload, LOCK_EX) !== false;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->directory.'/*.cache');
        if ($files === false) {
            return true;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * @param  iterable<string>  $keys
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

    /**
     * @param  iterable<string, mixed>  $values
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $allSuccess = true;
        foreach ($values as $key => $value) {
            if (! $this->set((string) $key, $value, $ttl)) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * @param  iterable<string>  $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $allSuccess = true;
        foreach ($keys as $key) {
            if (! $this->delete((string) $key)) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    public function has(string $key): bool
    {
        return $this->get($key, $this) !== $this;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function remember(string $key, null|int|DateInterval $ttl, callable $callback): mixed
    {
        $value = $this->get($key, $this);

        if ($value !== $this) {
            return $value;
        }

        $computed = $callback();
        $this->set($key, $computed, $ttl);

        return $computed;
    }

    private function getFilePath(string $key): string
    {
        return $this->directory.'/'.sha1($key).'.cache';
    }

    private function calculateExpiration(null|int|DateInterval $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            $now = new DateTime;
            $now->add($ttl);

            return $now->getTimestamp();
        }

        return time() + $ttl;
    }
}
