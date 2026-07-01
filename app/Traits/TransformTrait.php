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
        if ($this->fractalManager === null) {
            $this->fractalManager = new Manager;
        }

        return $this->fractalManager;
    }

    public function item(Response $response, mixed $data, TransformerAbstract $transformer, ?string $resourceKey = null): Response
    {
        $resource = new Item($data, $transformer, $resourceKey);

        return $this->json($response, $this->getFractalManager()->createData($resource)->toArray());
    }

    public function collection(Response $response, mixed $data, TransformerAbstract $transformer, ?string $resourceKey = null): Response
    {
        $resource = new Collection($data, $transformer, $resourceKey);

        return $this->json($response, $this->getFractalManager()->createData($resource)->toArray());
    }

    public function paginatedCollection(
        Response $response,
        LengthAwarePaginator $paginator,
        TransformerAbstract $transformer,
        ?string $resourceKey = null
    ): Response {
        $resource = new Collection($paginator->getCollection(), $transformer, $resourceKey);
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return $this->json($response, $this->getFractalManager()->createData($resource)->toArray());
    }
}
