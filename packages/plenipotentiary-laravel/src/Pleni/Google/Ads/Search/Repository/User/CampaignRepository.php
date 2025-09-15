<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\User;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\CampaignDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\Generated\CampaignRepository as GeneratedRepository;

/**
 * User-level CampaignRepository.
 * Extend and override methods as needed; this file is safe from regeneration.
 *
 * Responsibilities:
 * - Safe place for domain-specific persistence logic.
 * - Translate Domain DTOs to/from Eloquent or other storage.
 * - Compose Generated repository methods for consistency.
 */
class CampaignRepository extends GeneratedRepository
{
    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof CampaignDomainData);

        return parent::save($domainDto);
    }

    public function findById(int $id): ?CampaignDomainData
    {
        $model = \App\Models\Search\Campaign::find($id);

        return $model ? CampaignDomainData::fromModel($model) : null;
    }

    public function findOrCreateFromDto(CampaignDomainData $dto): CampaignDomainData
    {
        $model = \App\Models\Search\Campaign::updateOrCreate(
            ['campaign_id' => $dto->campaignId],
            [
                'name' => $dto->name,
                'status' => $dto->status,
                'resource_name' => $dto->resourceName,
                'budget_resource_name' => $dto->budgetResourceName,
            ]
        );

        return CampaignDomainData::fromModel($model);
    }

    public function getAll(array $filters = []): array
    {
        $query = \App\Models\Search\Campaign::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get()->map(
            fn ($model) => CampaignDomainData::fromModel($model)
        )->all();
    }

    public function update(int $id, array $data): bool
    {
        $model = \App\Models\Search\Campaign::find($id);
        if (! $model) {
            return false;
        }

        return $model->update($data);
    }
}
