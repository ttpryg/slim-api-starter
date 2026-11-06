<?php

declare(strict_types=1);

namespace App\Commands;

use App\Database\Migrator;
use App\Database\Seeder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:refresh',
    description: 'Reset and re-run all database migrations',
)]
class MigrateRefreshCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('seed', null, InputOption::VALUE_NONE, 'Run database seeders after refreshing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrationsPath = __DIR__.'/../../db/migrations';

        $migrator = new Migrator;

        $output->writeln('<comment>Rolling back all migrations...</comment>');
        $rolledBack = $migrator->reset($migrationsPath);

        if ($rolledBack === []) {
            $output->writeln('<info>Nothing to rollback.</info>');
        } else {
            foreach ($rolledBack as $file) {
                $output->writeln("<info>Rolled back:</info> {$file}");
            }
        }

        $output->writeln('<comment>Running all migrations...</comment>');
        $migrated = $migrator->run($migrationsPath);

        if ($migrated === []) {
            $output->writeln('<info>Nothing to migrate.</info>');
        } else {
            foreach ($migrated as $file) {
                $output->writeln("<info>Migrated:</info> {$file}");
            }
        }

        if ($input->getOption('seed')) {
            $output->writeln('<comment>Running database seeders...</comment>');
            $seedsPath = __DIR__.'/../../db/seeds';

            if (is_dir($seedsPath)) {
                $files = scandir($seedsPath);
                $files = is_array($files) ? array_diff($files, ['.', '..', '.gitkeep']) : [];
                sort($files);

                foreach ($files as $file) {
                    $filePath = $seedsPath.'/'.$file;
                    $seeder = require $filePath;

                    if ($seeder instanceof Seeder) {
                        $seeder->run();
                        $output->writeln("<info>Seeded:</info> {$file}");
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
