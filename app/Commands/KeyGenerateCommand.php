<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'key:generate',
    description: 'Generate a random APP_KEY (32 characters for AES-256-CBC)',
)]
class KeyGenerateCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('show', 's', InputOption::VALUE_NONE, 'Display the key instead of writing to .env');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing APP_KEY');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = bin2hex(random_bytes(16));

        if ($input->getOption('show')) {
            $output->writeln($key);

            return Command::SUCCESS;
        }

        $envPath = __DIR__.'/../../.env';

        if (! file_exists($envPath)) {
            $output->writeln('<error>.env file not found.</error>');

            return Command::FAILURE;
        }

        $content = file_get_contents($envPath);

        if (preg_match('/^APP_KEY=/m', $content)) {
            if (preg_match('/^APP_KEY=.+/m', $content) && ! $input->getOption('force')) {
                $output->writeln('<comment>APP_KEY already set. Use --force to overwrite.</comment>');

                return Command::SUCCESS;
            }

            $content = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$key}", $content);
        } else {
            $content .= PHP_EOL."APP_KEY={$key}".PHP_EOL;
        }

        file_put_contents($envPath, $content);

        $output->writeln('<info>APP_KEY generated and saved to .env</info>');

        return Command::SUCCESS;
    }
}
