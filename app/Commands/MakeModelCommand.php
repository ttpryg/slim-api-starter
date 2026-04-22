<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:model',
    description: 'Creates a new Eloquent model class',
)]
class MakeModelCommand extends GeneratorCommand
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the model class (e.g., User/Profile)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $details = $this->resolveClassDetails($name, 'App\\Model', __DIR__.'/../Model');

        if (! $details) {
            $output->writeln('<error>Invalid model name.</error>');

            return Command::FAILURE;
        }

        extract($details);

        if (! $this->makeDirectory($path, $output)) {
            return Command::FAILURE;
        }

        if (file_exists($filePath)) {
            $output->writeln("<error>Model {$name} already exists!</error>");

            return Command::FAILURE;
        }

        $stub = <<<EOF
<?php

declare(strict_types=1);

namespace {$namespace};

use Illuminate\Database\Eloquent\Model;

class {$className} extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    // protected \$table = 'table_name';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected \$fillable = [
        // 'column_name',
    ];
}

EOF;

        if (file_put_contents($filePath, $stub) === false) {
            $output->writeln("<error>Failed to write model file to {$filePath}</error>");

            return Command::FAILURE;
        }

        $output->writeln("<info>Model created successfully: {$namespace}\\{$className}</info>");

        return Command::SUCCESS;
    }
}
