<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'logs:clear',
    description: 'Clears all application log files',
)]
class LogsClearCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logsPath = __DIR__.'/../../storage/logs';

        if (! is_dir($logsPath)) {
            $output->writeln('<info>Logs directory not found. Nothing to clear.</info>');

            return Command::SUCCESS;
        }

        $files = glob($logsPath.'/*.log');

        if (empty($files)) {
            $output->writeln('<info>No log files found.</info>');

            return Command::SUCCESS;
        }

        $deletedCount = 0;
        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                $deletedCount++;
            }
        }

        $output->writeln("<info>Successfully cleared {$deletedCount} log file(s).</info>");

        return Command::SUCCESS;
    }
}
