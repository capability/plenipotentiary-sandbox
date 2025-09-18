<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Repository;

use Plenipotentiary\Laravel\Contracts\RepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\DTO\AdGroupDomainDTO;

class AdGroupRepository implements RepositoryContract
{
    public function all(array $criteria = []): iterable
    {
        return \App\Models\Search\AdGroup::query()
            ->when($criteria, fn ($q) => $q->where($criteria))
            ->get()
            ->map(fn ($model) => AdGroupDomainDTO::fromModel($model))
            ->all();
    }

    public function find(string|int $id): ?object
    {
        $model = \App\Models\Search\AdGroup::find($id);

        return $model ? AdGroupDomainDTO::fromModel($model) : null;
    }

    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof AdGroupDomainDTO);

        $model = \App\Models\Search\AdGroup::updateOrCreate(
            ['resource_name' => $domainDto->resourceName],
            ['name' => $domainDto->name, 'status' => $domainDto->status],
        );

        return AdGroupDomainDTO::fromModel($model);
    }

    public function delete(string|int $id): bool
    {
        if (! $model = \App\Models\Search\AdGroup::find($id)) {
            return false;
        }

        return (bool) $model->delete();
    }
}
