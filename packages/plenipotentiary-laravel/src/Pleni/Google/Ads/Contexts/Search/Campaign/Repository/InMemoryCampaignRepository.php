<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository;

use Illuminate\Support\Collection;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

/**
 * In-memory repository implementation of CampaignRepositoryContract.
 *
 * ⚠️ Intended for local development, testing, or as a basis for NoSQL/document-store style persistence.
 * This does not persist data between requests or processes.
 */
final class InMemoryCampaignRepository implements CampaignRepositoryContract
{
    /** @var array<string,CampaignCanonicalDTO> */
    private array $store = [];

    public function all(): Collection
    {
        return collect(array_values($this->store));
    }

    public function findById(string $id): ?CampaignCanonicalDTO
    {
        return $this->store[$id] ?? null;
    }

    public function findByExternalReference(string $externalRef): ?CampaignCanonicalDTO
    {
        return collect($this->store)->firstWhere(
            fn (CampaignCanonicalDTO $c) => $c->externalId === $externalRef
        );
    }

    public function save(CampaignCanonicalDTO $campaign): CampaignCanonicalDTO
    {
        $id = $campaign->externalId ?? uniqid('cmp_', true);
        $campaign->externalId = $id;
        $this->store[$id] = $campaign;

        return $campaign;
    }

    public function delete(string $id): bool
    {
        if (! isset($this->store[$id])) {
            return false;
        }
        unset($this->store[$id]);

        return true;
    }
}
