<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Psr\Http\Message\ResponseInterface as Response;

trait TransformTrait
{
    use ResponseTrait;

    private ?Manager $fractalManager = null;

    public function setFractalManager(Manager $manager): void
    {
        $this->fractalManager = $manager;
    }

    protected function getFractalManager(): Manager
    {
        $this->fractalManager ??= new Manager;

        return $this->fractalManager;
    }

    public function item(Response $response, mixed $data, TransformerAbstract $transformerAbstract, ?string $resourceKey = null): Response
    {
        $item = new Item($data, $transformerAbstract, $resourceKey);

        return $this->json($response, $this->getFractalManager()->createData($item)->toArray());
    }

    public function collection(Response $response, mixed $data, TransformerAbstract $transformerAbstract, ?string $resourceKey = null): Response
    {
        $collection = new Collection($data, $transformerAbstract, $resourceKey);

        return $this->json($response, $this->getFractalManager()->createData($collection)->toArray());
    }

    public function paginatedCollection(
        Response $response,
        LengthAwarePaginator $lengthAwarePaginator,
        TransformerAbstract $transformerAbstract,
        ?string $resourceKey = null
    ): Response {
        $collection = new Collection($lengthAwarePaginator->getCollection(), $transformerAbstract, $resourceKey);
        $collection->setPaginator(new IlluminatePaginatorAdapter($lengthAwarePaginator));

        return $this->json($response, $this->getFractalManager()->createData($collection)->toArray());
    }
}
