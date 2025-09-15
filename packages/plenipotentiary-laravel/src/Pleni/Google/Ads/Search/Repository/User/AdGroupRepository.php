<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\User;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\Generated\AdGroupRepository as GeneratedRepository;

/**
 * User-level AdGroupRepository.
 * Extend and override methods as needed; this file is safe from regeneration.
 */
class AdGroupRepository extends GeneratedRepository
{
    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof AdGroupDomainData);

        return parent::save($domainDto);
    }

    public function findById(int $id): ?AdGroupDomainData
    {
        $model = \App\Models\Search\AdGroup::find($id);

        return $model ? AdGroupDomainData::fromModel($model) : null;
    }

    public function findOrCreateFromDto(AdGroupDomainData $dto): AdGroupDomainData
    {
        $model = \App\Models\Search\AdGroup::updateOrCreate(
            ['resource_name' => $dto->resourceName],
            [
                'name' => $dto->name,
                'status' => $dto->status,
            ]
        );

        return AdGroupDomainData::fromModel($model);
    }

    public function getAll(array $filters = [], ?int $limit = null): array
    {
        $query = \App\Models\Search\AdGroup::query();

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(
            fn ($model) => AdGroupDomainData::fromModel($model)
        )->all();
    }

    public function update(int $id, array $data): bool
    {
        $model = \App\Models\Search\AdGroup::find($id);
        if (! $model) {
            return false;
        }

        return $model->update($data);
    }
}
