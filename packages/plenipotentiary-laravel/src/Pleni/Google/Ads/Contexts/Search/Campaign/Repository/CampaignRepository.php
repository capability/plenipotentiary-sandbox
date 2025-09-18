<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignDomainDTO;

/**
 * User-level CampaignRepository.
 * Extend and override methods as needed; this file is safe from regeneration.
 *
 * Responsibilities:
 * - Safe place for domain-specific persistence logic.
 * - Translate Domain DTOs to/from Eloquent or other storage.
 * - Compose Generated repository methods for consistency.
 */
class CampaignRepository
{
    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof CampaignDomainDTO);

        return parent::save($domainDto);
    }

    public function findById(int $id): ?CampaignDomainDTO
    {
        $model = \App\Models\Search\Campaign::find($id);

        return $model ? CampaignDomainDTO::fromModel($model) : null;
    }

    public function findOrCreateFromDto(CampaignDomainDTO $dto): CampaignDomainDTO
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

        return CampaignDomainDTO::fromModel($model);
    }

    public function getAll(array $filters = []): array
    {
        $query = \App\Models\Search\Campaign::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get()->map(
            fn ($model) => CampaignDomainDTO::fromModel($model)
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
