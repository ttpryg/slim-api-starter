<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'seed:create',
    description: 'Creates a new database seeder',
)]
class SeedCreateCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the seeder (e.g., UsersTableSeeder)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $path = __DIR__.'/../../db/seeds';
        $this->ensureDirectory($path);

        $filename = $name.'.php';
        $filePath = $path.'/'.$filename;

        $stub = <<<PHP
<?php

declare(strict_types=1);

use App\Database\Seeder;

return new class extends Seeder
{
    public function run(): void
    {
        //
    }
};

PHP;

        file_put_contents($filePath, $stub);

        $output->writeln("<info>Seeder created:</info> {$filename}");

        return Command::SUCCESS;
    }

    private function ensureDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
