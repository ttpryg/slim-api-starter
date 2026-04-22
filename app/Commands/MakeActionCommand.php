<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:action',
    description: 'Creates a new action class',
)]
class MakeActionCommand extends GeneratorCommand
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the action class (e.g., User/LoginAction)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $details = $this->resolveClassDetails($name, 'App\\Action', __DIR__.'/../Action', 'Action');

        if (! $details) {
            $output->writeln('<error>Invalid action name.</error>');

            return Command::FAILURE;
        }

        extract($details);

        if (! $this->makeDirectory($path, $output)) {
            return Command::FAILURE;
        }

        if (file_exists($filePath)) {
            $output->writeln("<error>Action {$name} already exists!</error>");

            return Command::FAILURE;
        }

        $stub = <<<EOF
<?php

declare(strict_types=1);

namespace {$namespace};

use App\Traits\ResponseTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class {$className}
{
    use ResponseTrait;

    public function __invoke(Request \$request, Response \$response): Response
    {
        // TODO: Implement action logic here
        
        return \$this->success(\$response, null, 'Action executed successfully');
    }
}

EOF;

        if (file_put_contents($filePath, $stub) === false) {
            $output->writeln("<error>Failed to write action file to {$filePath}</error>");

            return Command::FAILURE;
        }

        $output->writeln("<info>Action created successfully: {$namespace}\\{$className}</info>");

        return Command::SUCCESS;
    }
}
