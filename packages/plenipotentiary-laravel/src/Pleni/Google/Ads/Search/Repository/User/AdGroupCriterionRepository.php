<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\User;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupCriterionDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\Generated\AdGroupCriterionRepository as GeneratedRepository;

/**
 * User-level AdGroupCriterionRepository.
 * Extend and override methods as needed; this file is safe from regeneration.
 */
class AdGroupCriterionRepository extends GeneratedRepository
{
    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof AdGroupCriterionDomainData);

        return parent::save($domainDto);
    }

    public function findById(int $id): ?AdGroupCriterionDomainData
    {
        $model = \App\Models\Search\AdGroupCriterion::find($id);

        return $model ? AdGroupCriterionDomainData::fromModel($model) : null;
    }

    public function findOrCreateFromDto(AdGroupCriterionDomainData $dto): AdGroupCriterionDomainData
    {
        $model = \App\Models\Search\AdGroupCriterion::updateOrCreate(
            ['criterion_id' => $dto->criterionId],
            [
                'keyword_text' => $dto->keywordText,
                'match_type' => $dto->matchType,
                'status' => $dto->status,
                'resource_name' => $dto->resourceName,
                'adgroup_pkid' => $dto->parentAdGroupResourceName,
            ]
        );

        return AdGroupCriterionDomainData::fromModel($model);
    }

    public function getAll(array $filters = [], ?int $limit = null): array
    {
        $query = \App\Models\Search\AdGroupCriterion::query();

        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(
            fn ($model) => AdGroupCriterionDomainData::fromModel($model)
        )->all();
    }

    public function update(int $id, array $data): bool
    {
        $model = \App\Models\Search\AdGroupCriterion::find($id);
        if (! $model) {
            return false;
        }

        return $model->update($data);
    }

    public function getNewForSync(?int $limit = null): array
    {
        $query = \App\Models\Search\AdGroupCriterion::whereNull('resource_name')
            ->whereHas('adgroup', fn ($q) => $q->whereNotNull('resource_name'));

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(
            fn ($model) => AdGroupCriterionDomainData::fromModel($model)
        )->all();
    }

    public function getExistingForSync(?int $limit = null): array
    {
        $query = \App\Models\Search\AdGroupCriterion::whereNotNull('resource_name')
            ->whereHas('adgroup', fn ($q) => $q->whereNotNull('resource_name'));

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(
            fn ($model) => AdGroupCriterionDomainData::fromModel($model)
        )->all();
    }
}
