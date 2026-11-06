<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:job',
    description: 'Creates a new queue job class',
)]
class MakeJobCommand extends GeneratorCommand
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the job class (e.g., SendWelcomeEmailJob)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $details = $this->resolveClassDetails($name, 'App\\Jobs', __DIR__.'/../Jobs', 'Job');

        if (! $details) {
            $output->writeln('<error>Invalid job name.</error>');

            return Command::FAILURE;
        }

        extract($details);

        if (! $this->makeDirectory($path, $output)) {
            return Command::FAILURE;
        }

        if (file_exists($filePath)) {
            $output->writeln("<error>Job {$name} already exists!</error>");

            return Command::FAILURE;
        }

        $stub = <<<EOF
<?php

declare(strict_types=1);

namespace {$namespace};

use Ttpryg\Queue\BaseJob;

final class {$className} extends BaseJob
{
    public function process(): void
    {
        // TODO: Handle asynchronous job processing
    }
}

EOF;

        if (file_put_contents($filePath, $stub) === false) {
            $output->writeln("<error>Failed to create job file at {$filePath}</error>");

            return Command::FAILURE;
        }

        $output->writeln("<info>Job created successfully:</info> {$filePath}");

        return Command::SUCCESS;
    }
}
