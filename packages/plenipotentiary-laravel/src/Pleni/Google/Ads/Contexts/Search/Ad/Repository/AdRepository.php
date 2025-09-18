<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Repository;

use Plenipotentiary\Laravel\Contracts\RepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\DTO\AdDomainDTO;

class AdRepository implements RepositoryContract
{
    public function all(array $criteria = []): iterable
    {
        return \App\Models\Search\Ad::query()
            ->when($criteria, fn ($q) => $q->where($criteria))
            ->get()
            ->map(fn ($model) => AdDomainDTO::fromModel($model))
            ->all();
    }

    public function find(string|int $id): ?object
    {
        $model = \App\Models\Search\Ad::find($id);

        return $model ? AdDomainDTO::fromModel($model) : null;
    }

    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof AdDomainDTO);

        $model = \App\Models\Search\Ad::updateOrCreate(
            ['resource_name' => $domainDto->resourceName],
            ['status' => $domainDto->status],
        );

        return AdDomainDTO::fromModel($model);
    }

    public function delete(string|int $id): bool
    {
        if (! $model = \App\Models\Search\Ad::find($id)) {
            return false;
        }

        return (bool) $model->delete();
    }
}
