<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\Generated;

use Plenipotentiary\Laravel\Contracts\RepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\CampaignDomainData;

/**
 * Generated CampaignRepository with stub methods.
 * This file may be overwritten by scaffolding.
 *
 * Responsibilities:
 * - Provide generic persistence operations (all, find, save, delete).
 * - Boilerplate only, may be regenerated.
 * - Should NOT contain domain-specific rules.
 */
class CampaignRepository implements RepositoryContract
{
    public function all(array $criteria = []): iterable
    {
        // TODO: Implement all() for Campaign
        return [];
    }

    public function find(string|int $id): ?object
    {
        // TODO: Implement find() for Campaign
        return null;
    }

    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof CampaignDomainData);

        // TODO: Implement save() for Campaign
        return $domainDto;
    }

    public function delete(string|int $id): bool
    {
        // TODO: Implement delete() for Campaign
        return false;
    }
}
