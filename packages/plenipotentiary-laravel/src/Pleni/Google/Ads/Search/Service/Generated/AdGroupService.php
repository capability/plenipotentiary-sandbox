<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\Generated;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\AdGroupExternalData;

/**
 * Generated service with raw Google Ads AdGroup API calls.
 * This class may be overwritten on regeneration.
 */
class AdGroupService
{
    public function createAdGroup(AdGroupDomainData $domainDto): AdGroupExternalData
    {
        // TODO: wire into Google Ads mutate logic
        throw new \RuntimeException('Not yet implemented');
    }

    public function getAdGroup(string $id): ?AdGroupExternalData
    {
        // TODO: Google Ads API get by ID
        return null;
    }

    public function updateAdGroup(AdGroupDomainData $domainDto): AdGroupExternalData
    {
        // TODO: wire into Google Ads mutate logic for update
        throw new \RuntimeException('Not yet implemented');
    }

    public function removeAdGroup(string $id): bool
    {
        // TODO: wire into Google Ads API remove
        return false;
    }
}
