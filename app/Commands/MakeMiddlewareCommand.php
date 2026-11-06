<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:middleware',
    description: 'Creates a new PSR-15 middleware class',
)]
class MakeMiddlewareCommand extends GeneratorCommand
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the middleware class (e.g., Auth/CheckApiKeyMiddleware)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $details = $this->resolveClassDetails($name, 'App\\Middleware', __DIR__.'/../Middleware', 'Middleware');

        if (! $details) {
            $output->writeln('<error>Invalid middleware name.</error>');

            return Command::FAILURE;
        }

        extract($details);

        if (! $this->makeDirectory($path, $output)) {
            return Command::FAILURE;
        }

        if (file_exists($filePath)) {
            $output->writeln("<error>Middleware {$name} already exists!</error>");

            return Command::FAILURE;
        }

        $stub = <<<EOF
<?php

declare(strict_types=1);

namespace {$namespace};

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final readonly class {$className} implements MiddlewareInterface
{
    public function process(Request \$request, RequestHandler \$handler): Response
    {
        // TODO: Implement middleware logic before request handler

        \$response = \$handler->handle(\$request);

        // TODO: Implement middleware logic after request handler

        return \$response;
    }
}

EOF;

        if (file_put_contents($filePath, $stub) === false) {
            $output->writeln("<error>Failed to create middleware file at {$filePath}</error>");

            return Command::FAILURE;
        }

        $output->writeln("<info>Middleware created successfully:</info> {$filePath}");

        return Command::SUCCESS;
    }
}
