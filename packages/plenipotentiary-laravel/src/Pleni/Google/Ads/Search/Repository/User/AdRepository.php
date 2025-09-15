<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\User;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\Generated\AdRepository as GeneratedRepository;

/**
 * User-level AdRepository.
 * Extend and override methods as needed; this file is safe from regeneration.
 */
class AdRepository extends GeneratedRepository
{
    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof AdDomainData);

        return parent::save($domainDto);
    }

    public function findById(int $id): ?AdDomainData
    {
        $model = \App\Models\Search\Ad::find($id);

        return $model ? AdDomainData::fromModel($model) : null;
    }

    public function findOrCreateFromDto(AdDomainData $dto): AdDomainData
    {
        $model = \App\Models\Search\Ad::updateOrCreate(
            ['ad_id' => $dto->adId],
            [
                'resource_name' => $dto->resourceName,
                'status' => $dto->status,
            ]
        );

        return AdDomainData::fromModel($model);
    }

    public function getAll(array $filters = [], ?int $limit = null): array
    {
        $query = \App\Models\Search\Ad::query();

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(
            fn ($model) => AdDomainData::fromModel($model)
        )->all();
    }

    public function update(int $id, array $data): bool
    {
        $model = \App\Models\Search\Ad::find($id);
        if (! $model) {
            return false;
        }

        return $model->update($data);
    }

    /**
     * Ads to CREATE: have no resource_name but parent AdGroup does.
     */
    public function getNewForSync(?int $limit = null): array
    {
        $query = \App\Models\Search\Ad::query()
            ->whereNull('resource_name')
            ->whereHas('adgroup', function ($q) {
                $q->whereNotNull('resource_name');
            });

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(
            fn ($model) => AdDomainData::fromModel($model)
        )->all();
    }

    /**
     * Ads to UPDATE: have a resource_name and parent AdGroup has resource_name.
     */
    public function getExistingForSync(?int $limit = null): array
    {
        $query = \App\Models\Search\Ad::whereHas('adgroup', function ($q) {
            $q->whereNotNull('resource_name');
        });

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(
            fn ($model) => AdDomainData::fromModel($model)
        )->all();
    }
}
