<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:factory',
    description: 'Creates a new model factory class',
)]
class MakeFactoryCommand extends GeneratorCommand
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the factory class (e.g., UserFactory)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $details = $this->resolveClassDetails($name, 'Database\\Factories', __DIR__.'/../../db/factories', 'Factory');

        if (! $details) {
            $output->writeln('<error>Invalid factory name.</error>');

            return Command::FAILURE;
        }

        extract($details);

        if (! $this->makeDirectory($path, $output)) {
            return Command::FAILURE;
        }

        if (file_exists($filePath)) {
            $output->writeln("<error>Factory {$name} already exists!</error>");

            return Command::FAILURE;
        }

        $modelName = str_replace('Factory', '', $className);

        $stub = <<<EOF
<?php

declare(strict_types=1);

namespace {$namespace};

use App\Database\Factory;
use App\Model\\{$modelName};

/**
 * @extends Factory<{$modelName}>
 */
final class {$className} extends Factory
{
    public function model(): string
    {
        return {$modelName}::class;
    }

    public function definition(): array
    {
        return [
            // 'name' => \$this->faker->name(),
        ];
    }
}

EOF;

        if (file_put_contents($filePath, $stub) === false) {
            $output->writeln("<error>Failed to create factory file at {$filePath}</error>");

            return Command::FAILURE;
        }

        $output->writeln("<info>Factory created successfully:</info> {$filePath}");

        return Command::SUCCESS;
    }
}
