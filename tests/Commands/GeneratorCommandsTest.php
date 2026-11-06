<?php

declare(strict_types=1);

namespace App\Test\Commands;

use App\Commands\MakeMiddlewareCommand;
use App\Commands\MakeTransformerCommand;
use App\Commands\SeedCreateCommand;
use App\Test\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GeneratorCommandsTest extends TestCase
{
    private string $tempMiddleware;

    private string $tempTransformer;

    private string $tempSeeder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempMiddleware = __DIR__.'/../../app/Middleware/TestDummyMiddleware.php';
        $this->tempTransformer = __DIR__.'/../../app/Transformer/TestDummyTransformer.php';
        $this->tempSeeder = __DIR__.'/../../db/seeds/TestDummySeeder.php';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempMiddleware)) {
            unlink($this->tempMiddleware);
        }
        if (file_exists($this->tempTransformer)) {
            unlink($this->tempTransformer);
        }
        if (file_exists($this->tempSeeder)) {
            unlink($this->tempSeeder);
        }
        parent::tearDown();
    }

    public function test_make_middleware_command(): void
    {
        $application = new Application;
        $application->add(new MakeMiddlewareCommand);

        $command = $application->find('make:middleware');
        $tester = new CommandTester($command);
        $tester->execute(['name' => 'TestDummyMiddleware']);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFileExists($this->tempMiddleware);
        $this->assertStringContainsString('class TestDummyMiddleware implements MiddlewareInterface', (string) file_get_contents($this->tempMiddleware));
    }

    public function test_make_transformer_command(): void
    {
        $application = new Application;
        $application->add(new MakeTransformerCommand);

        $command = $application->find('make:transformer');
        $tester = new CommandTester($command);
        $tester->execute(['name' => 'TestDummyTransformer']);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFileExists($this->tempTransformer);
        $this->assertStringContainsString('class TestDummyTransformer extends TransformerAbstract', (string) file_get_contents($this->tempTransformer));
    }

    public function test_seed_create_command_and_make_seeder_alias(): void
    {
        $application = new Application;
        $application->add(new SeedCreateCommand);

        $command = $application->find('make:seeder');
        $tester = new CommandTester($command);
        $tester->execute(['name' => 'TestDummySeeder']);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFileExists($this->tempSeeder);
        $this->assertStringContainsString('new class extends Seeder', (string) file_get_contents($this->tempSeeder));
    }
}
