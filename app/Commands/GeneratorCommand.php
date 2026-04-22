<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GeneratorCommand extends Command
{
    /**
     * Convert a string to StudlyCaps (PascalCase).
     */
    protected function studly(string $string): string
    {
        $string = ucwords(str_replace(['-', '_'], ' ', $string));

        return str_replace(' ', '', $string);
    }

    /**
     * Resolve the class name, namespace, and file path from the input name.
     *
     * @return array{className: string, namespace: string, path: string, filePath: string}|null
     */
    protected function resolveClassDetails(
        string $name,
        string $baseNamespace,
        string $basePath,
        string $suffix = ''
    ): ?array {
        // Remove .php extension if provided
        $name = str_replace('.php', '', $name);

        $parts = array_filter(explode('/', str_replace('\\', '/', $name)), fn ($p) => ! in_array($p, ['', '.', '..'], true));
        $parts = array_map([$this, 'studly'], $parts);
        $className = array_pop($parts);

        if (! $className) {
            return null;
        }

        // Ensure class name ends with suffix if provided
        if ($suffix !== '' && ! str_ends_with($className, $suffix)) {
            $className .= $suffix;
        }

        $namespace = $baseNamespace;
        $path = $basePath;

        if (! empty($parts)) {
            $subNamespace = implode('\\', $parts);
            $namespace .= '\\'.$subNamespace;
            $path .= '/'.implode('/', $parts);
        }

        $filePath = $path.'/'.$className.'.php';

        return [
            'className' => $className,
            'namespace' => $namespace,
            'path' => $path,
            'filePath' => $filePath,
        ];
    }

    /**
     * Ensure the target directory exists.
     */
    protected function makeDirectory(string $path, OutputInterface $output): bool
    {
        if (! is_dir($path) && ! mkdir($path, 0755, true) && ! is_dir($path)) {
            $output->writeln("<error>Failed to create directory: {$path}</error>");

            return false;
        }

        return true;
    }
}
