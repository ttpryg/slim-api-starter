<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:transformer',
    description: 'Creates a new Fractal resource transformer class',
)]
class MakeTransformerCommand extends GeneratorCommand
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the transformer class (e.g., PostTransformer)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $details = $this->resolveClassDetails($name, 'App\\Transformer', __DIR__.'/../Transformer', 'Transformer');

        if (! $details) {
            $output->writeln('<error>Invalid transformer name.</error>');

            return Command::FAILURE;
        }

        extract($details);

        if (! $this->makeDirectory($path, $output)) {
            return Command::FAILURE;
        }

        if (file_exists($filePath)) {
            $output->writeln("<error>Transformer {$name} already exists!</error>");

            return Command::FAILURE;
        }

        $stub = <<<EOF
<?php

declare(strict_types=1);

namespace {$namespace};

use League\Fractal\TransformerAbstract;

final class {$className} extends TransformerAbstract
{
    /**
     * @param mixed \$item
     * @return array<string, mixed>
     */
    public function transform(mixed \$item): array
    {
        return [
            // 'id' => \$item->id ?? \$item['id'],
        ];
    }
}

EOF;

        if (file_put_contents($filePath, $stub) === false) {
            $output->writeln("<error>Failed to create transformer file at {$filePath}</error>");

            return Command::FAILURE;
        }

        $output->writeln("<info>Transformer created successfully:</info> {$filePath}");

        return Command::SUCCESS;
    }
}
