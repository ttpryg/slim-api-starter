<?php

declare(strict_types=1);

namespace App\Database;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class Factory
{
    protected Generator $faker;

    protected int $count = 1;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
    }

    /**
     * @return class-string<TModel>
     */
    abstract public function model(): string;

    /**
     * @return array<string, mixed>
     */
    abstract public function definition(): array;

    public function count(int $count): static
    {
        $clone = clone $this;
        $clone->count = $count;

        return $clone;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel|Collection<int, TModel>
     */
    public function make(array $attributes = []): Model|Collection
    {
        if ($this->count === 1) {
            $class = $this->model();

            return new $class(array_merge($this->definition(), $attributes));
        }

        $results = [];
        for ($i = 0; $i < $this->count; $i++) {
            $class = $this->model();
            $results[] = new $class(array_merge($this->definition(), $attributes));
        }

        return new Collection($results);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel|Collection<int, TModel>
     */
    public function create(array $attributes = []): Model|Collection
    {
        $instances = $this->make($attributes);

        if ($instances instanceof Model) {
            $instances->save();

            return $instances;
        }

        foreach ($instances as $instance) {
            $instance->save();
        }

        return $instances;
    }
}
