<?php

declare(strict_types=1);

namespace App\Commands;

use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'seed:run',
    description: 'Run database seeder',
)]
class SeedRunCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the seeder (e.g., SampleClassSeeder)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $phinx = new PhinxApplication;
        $phinx->setAutoExit(false); // Prevent Phinx from forcefully calling exit()

        $arguments = new ArrayInput([
            'command' => 'seed:run',
            'name' => $name,
        ]);

        return $phinx->run($arguments, $output);
    }
}
