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
class MakeActionCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the action class (e.g., User/LoginAction)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        
        // Remove .php extension if provided
        $name = str_replace('.php', '', $name);
        
        // Helper to convert snake_case or kebab-case to PascalCase
        $studly = function ($string) {
            $string = ucwords(str_replace(['-', '_'], ' ', $string));
            return str_replace(' ', '', $string);
        };

        $parts = explode('/', str_replace('\\', '/', $name));
        $parts = array_map($studly, $parts);
        $className = array_pop($parts);

        // Ensure class name ends with Action
        if (!str_ends_with($className, 'Action')) {
            $className .= 'Action';
        }

        $namespace = 'App\\Action';
        $path = __DIR__ . '/../Action';
        
        if (!empty($parts)) {
            $subNamespace = implode('\\', $parts);
            $namespace .= '\\' . $subNamespace;
            $path .= '/' . implode('/', $parts);
        }

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $filePath = $path . '/' . $className . '.php';

        if (file_exists($filePath)) {
            $output->writeln("<error>Action {$name} already exists!</error>");
            return Command::FAILURE;
        }

        $stub = <<<EOF
<?php

declare(strict_types=1);

namespace {$namespace};

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class {$className}
{
    public function __invoke(Request \$request, Response \$response): Response
    {
        // TODO: Implement action logic here
        
        \$response->getBody()->write(json_encode(['message' => 'Action executed successfully']));
        return \$response->withHeader('Content-Type', 'application/json');
    }
}

EOF;

        file_put_contents($filePath, $stub);

        $output->writeln("<info>Action created successfully: {$namespace}\\{$className}</info>");

        return Command::SUCCESS;
    }
}
