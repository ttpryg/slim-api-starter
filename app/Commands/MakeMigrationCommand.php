<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:migration',
    description: 'Creates a new database migration',
)]
class MakeMigrationCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the migration (e.g., CreateUsersTable)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $path = __DIR__.'/../../db/migrations';
        $this->ensureDirectory($path);

        $timestamp = date('Y_m_d_His');
        $filename = $timestamp.'_'.$this->snake($name).'.php';
        $filePath = $path.'/'.$filename;

        $stub = $this->buildStub($name);

        file_put_contents($filePath, $stub);

        $output->writeln("<info>Migration created:</info> {$filename}");

        return Command::SUCCESS;
    }

    private function buildStub(string $name): string
    {
        $table = $this->parseTableName($name);
        $action = $this->parseAction($name);

        $up = match ($action) {
            'create' => "\$this->schema->create('{$table}', function (\Illuminate\Database\Schema\Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
        });",
            'add', 'modify' => "\$this->schema->table('{$table}', function (\Illuminate\Database\Schema\Blueprint \$table) {
            \$table->string('column_name');
        });",
            'drop' => "\$this->schema->dropIfExists('{$table}');",
            default => '//',
        };

        $down = match ($action) {
            'create' => "\$this->schema->dropIfExists('{$table}');",
            'add', 'modify' => "\$this->schema->table('{$table}', function (\Illuminate\Database\Schema\Blueprint \$table) {
            \$table->dropColumn('column_name');
        });",
            'drop' => "\$this->schema->create('{$table}', function (\Illuminate\Database\Schema\Blueprint \$table) {
            \$table->id();
        });",
            default => '//',
        };

        return <<<PHP
<?php

declare(strict_types=1);

use App\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        {$up}
    }

    public function down(): void
    {
        {$down}
    }
};

PHP;
    }

    private function parseTableName(string $name): string
    {
        $patterns = [
            '/^Create([A-Za-z0-9_]+)Table$/',
            '/^(Add|Drop|Modify|Update|Alter)([A-Za-z0-9_]+)Table$/',
            '/^([A-Za-z0-9_]+)Table$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $name, $matches)) {
                $table = end($matches);

                return $this->snake($table);
            }
        }

        return $this->snake($name);
    }

    private function parseAction(string $name): string
    {
        if (preg_match('/^Create/i', $name)) {
            return 'create';
        }

        if (preg_match('/^Drop/i', $name)) {
            return 'drop';
        }

        if (preg_match('/^(Add|Modify|Update|Alter|Change|Rename)/i', $name)) {
            return 'add';
        }

        return 'modify';
    }

    private function snake(string $value): string
    {
        $value = preg_replace('/([A-Z])/', '_$1', $value);
        $value = trim($value, '_');

        return strtolower($value);
    }

    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
