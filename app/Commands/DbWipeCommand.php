<?php

declare(strict_types=1);

namespace App\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'db:wipe',
    description: 'Drop all database tables',
)]
class DbWipeCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation prompt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $input->getOption('force')) {
            $output->writeln('<question>Are you sure you want to drop all tables? Use --force to confirm.</question>');

            return Command::SUCCESS;
        }

        $schema = Capsule::schema();
        $driver = Capsule::connection()->getDriverName();

        $tables = match ($driver) {
            'sqlite' => Capsule::connection()->select("SELECT name FROM sqlite_master WHERE type = 'table'"),
            default => Capsule::connection()->select('SHOW TABLES'),
        };

        if (empty($tables)) {
            $output->writeln('<info>No tables found.</info>');

            return Command::SUCCESS;
        }

        $schema->disableForeignKeyConstraints();

        foreach ($tables as $row) {
            $name = $driver === 'sqlite' ? $row->name : current((array) $row);
            $schema->dropIfExists($name);
            $output->writeln("<info>Dropped:</info> {$name}");
        }

        $schema->enableForeignKeyConstraints();

        $output->writeln('<info>All tables dropped successfully.</info>');

        return Command::SUCCESS;
    }
}
