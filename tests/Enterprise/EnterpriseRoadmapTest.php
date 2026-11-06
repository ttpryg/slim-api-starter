<?php

declare(strict_types=1);

namespace App\Test\Enterprise;

use App\Cache\FileCache;
use App\Database\Factory;
use App\Queue\LazyCapsuleDatabaseAdapter;
use App\Storage\StorageManager;
use App\Test\TestCase;
use App\Test\Traits\DatabaseTransactions;
use App\Test\Traits\RefreshDatabase;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Ttpryg\Queue\BaseJob;
use Ttpryg\Queue\Config\QueueConfig;
use Ttpryg\Queue\QueueManager;

/**
 * @property string $name
 * @property string $email
 */
class DummyUserModel extends Model
{
    protected $table = 'dummy_users';

    protected $guarded = [];

    public $timestamps = false;
}

/**
 * @extends Factory<DummyUserModel>
 */
class DummyUserFactory extends Factory
{
    public function model(): string
    {
        return DummyUserModel::class;
    }

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
        ];
    }
}

class SampleTestJob extends BaseJob
{
    public static bool $executed = false;

    public function process(): void
    {
        self::$executed = true;
    }
}

class EnterpriseRoadmapTest extends TestCase
{
    use DatabaseTransactions;
    use RefreshDatabase;

    public function test_storage_manager_creates_and_reads_files(): void
    {
        $storageDir = __DIR__.'/../../storage/app/testing';
        $manager = new StorageManager([
            'testing' => [
                'driver' => 'local',
                'root' => $storageDir,
            ],
        ], 'testing');

        $disk = $manager->disk('testing');
        $disk->write('hello.txt', 'Slim 4 Starter with Flysystem');

        $this->assertTrue($disk->fileExists('hello.txt'));
        $this->assertSame('Slim 4 Starter with Flysystem', $disk->read('hello.txt'));

        $disk->delete('hello.txt');
        $this->assertFalse($disk->fileExists('hello.txt'));

        if (is_dir($storageDir)) {
            @rmdir($storageDir);
        }
    }

    public function test_file_cache_operations_and_remember(): void
    {
        $cacheDir = __DIR__.'/../../storage/cache/test_cache';
        $cache = new FileCache($cacheDir);

        $cache->set('test_key', 'test_value', 60);
        $this->assertTrue($cache->has('test_key'));
        $this->assertSame('test_value', $cache->get('test_key'));

        $called = false;
        $val = $cache->remember('remember_key', 60, function () use (&$called): string {
            $called = true;

            return 'computed_val';
        });

        $this->assertTrue($called);
        $this->assertSame('computed_val', $val);

        // Second call should fetch from cache, callback not executed
        $calledAgain = false;
        $valAgain = $cache->remember('remember_key', 60, function () use (&$calledAgain): string {
            $calledAgain = true;

            return 'new_val';
        });

        $this->assertFalse($calledAgain);
        $this->assertSame('computed_val', $valAgain);

        $cache->clear();
        $this->assertFalse($cache->has('test_key'));

        if (is_dir($cacheDir)) {
            @rmdir($cacheDir);
        }
    }

    public function test_model_factory_pattern(): void
    {
        Capsule::schema()->create('dummy_users', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
        });

        $factory = new DummyUserFactory;
        /** @var DummyUserModel $user */
        $user = $factory->create(['name' => 'John Doe']);

        $this->assertInstanceOf(DummyUserModel::class, $user);
        $this->assertSame('John Doe', $user->name);
        $this->assertNotEmpty($user->email);

        $collection = $factory->count(3)->create();
        $this->assertCount(3, $collection);

        Capsule::schema()->drop('dummy_users');
    }

    public function test_ttpryg_queue_push_and_pop(): void
    {
        $adapter = new LazyCapsuleDatabaseAdapter(new Capsule);
        $config = QueueConfig::fromArray([
            'defaultHandler' => 'database',
            'database' => [
                'table' => 'queue_jobs',
            ],
        ]);

        $queueManager = new QueueManager($config, $adapter);
        $queue = $queueManager->init();

        $result = $queue->push('default', SampleTestJob::class, ['foo' => 'bar']);
        $this->assertTrue($result->isSuccess);

        $job = $queue->pop('default');
        $this->assertNotNull($job);
        $this->assertSame(SampleTestJob::class, $job->job);

        $queue->done($job);
    }
}
