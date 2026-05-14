<?php

declare(strict_types=1);

namespace App\Commands;

use App\Database\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate',
    description: 'Run pending database migrations',
)]
class MigrateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = __DIR__.'/../../db/migrations';

        $migrator = new Migrator;
        $migrated = $migrator->run($path);

        if (empty($migrated)) {
            $output->writeln('<info>Nothing to migrate.</info>');

            return Command::SUCCESS;
        }

        foreach ($migrated as $file) {
            $output->writeln("<info>Migrated:</info> {$file}");
        }

        return Command::SUCCESS;
    }
}
