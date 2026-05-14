<?php

declare(strict_types=1);

namespace App\Commands;

use App\Database\Seeder;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'seed:run',
    description: 'Run database seeders',
)]
class SeedRunCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = __DIR__.'/../../db/seeds';

        if (! is_dir($path)) {
            $output->writeln('<error>Seeds directory not found.</error>');

            return Command::FAILURE;
        }

        $files = scandir($path);
        $files = array_diff($files, ['.', '..', '.gitkeep']);
        sort($files);

        if (empty($files)) {
            $output->writeln('<info>No seeders found.</info>');

            return Command::SUCCESS;
        }

        $capsule = $this->bootCapsule();

        foreach ($files as $file) {
            $filePath = $path.'/'.$file;
            $seeder = require $filePath;

            if (! $seeder instanceof Seeder) {
                $output->writeln("<error>Skipped (invalid): {$file}</error>");

                continue;
            }

            $seeder->setCapsule($capsule);
            $seeder->run();

            $output->writeln("<info>Seeded:</info> {$file}");
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
