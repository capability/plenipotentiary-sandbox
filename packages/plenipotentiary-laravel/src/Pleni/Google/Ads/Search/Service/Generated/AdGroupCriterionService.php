<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\Generated;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupCriterionDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\AdGroupCriterionExternalData;

/**
 * Generated service with raw Google Ads AdGroupCriterion API calls.
 * This file may be overwritten on regeneration.
 */
class AdGroupCriterionService
{
    public function createAdGroupCriterion(AdGroupCriterionDomainData $domainDto): AdGroupCriterionExternalData
    {
        // TODO: wire into Google Ads mutate logic
        throw new \RuntimeException('Not yet implemented');
    }

    public function getAdGroupCriterion(string $id): ?AdGroupCriterionExternalData
    {
        // TODO: Google Ads API get by ID
        return null;
    }

    public function updateAdGroupCriterion(AdGroupCriterionDomainData $domainDto): AdGroupCriterionExternalData
    {
        // TODO: wire into Google Ads mutate logic for update
        throw new \RuntimeException('Not yet implemented');
    }

    public function removeAdGroupCriterion(string $id): bool
    {
        // TODO: wire into Google Ads API remove
        return false;
    }
}
