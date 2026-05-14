<?php

declare(strict_types=1);

namespace App\Commands;

use App\Database\Migrator;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:rollback',
    description: 'Rollback the last database migration batch',
)]
class MigrationRollbackCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('steps', InputArgument::OPTIONAL, 'Number of batches to rollback', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $steps = (int) $input->getArgument('steps');
        $path = __DIR__.'/../../db/migrations';

        $capsule = $this->bootCapsule();
        $migrator = new Migrator($capsule);

        $rolledBack = $migrator->rollback($path, $steps);

        if (empty($rolledBack)) {
            $output->writeln('<info>Nothing to rollback.</info>');

            return Command::SUCCESS;
        }

        foreach ($rolledBack as $file) {
            $output->writeln("<info>Rolled back:</info> {$file}");
        }

        return Command::SUCCESS;
    }

    protected function bootCapsule(): Capsule
    {
        $settings = require __DIR__.'/../../config/settings.php';

        $capsule = new Capsule(new Container);
        $capsule->addConnection($settings['database']);
        $capsule->bootEloquent();
        $capsule->setAsGlobal();

        return $capsule;
    }
}
