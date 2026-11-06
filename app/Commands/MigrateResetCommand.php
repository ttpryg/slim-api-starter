<?php

declare(strict_types=1);

namespace App\Commands;

use App\Database\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:reset',
    description: 'Rollback all database migrations',
)]
class MigrateResetCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = __DIR__.'/../../db/migrations';

        $migrator = new Migrator;
        $rolledBack = $migrator->reset($path);

        if ($rolledBack === []) {
            $output->writeln('<info>Nothing to rollback.</info>');

            return Command::SUCCESS;
        }

        foreach ($rolledBack as $file) {
            $output->writeln("<info>Rolled back:</info> {$file}");
        }

        return Command::SUCCESS;
    }
}
