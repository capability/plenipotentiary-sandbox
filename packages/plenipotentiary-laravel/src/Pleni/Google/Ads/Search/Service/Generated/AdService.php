<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\Generated;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\AdExternalData;

/**
 * Generated service with raw Google Ads Ad API calls.
 * This file may be overwritten on regeneration.
 */
class AdService
{
    public function createAd(AdDomainData $domainDto): AdExternalData
    {
        // TODO: wire into Google Ads mutate logic
        throw new \RuntimeException('Not yet implemented');
    }

    public function getAd(string $id): ?AdExternalData
    {
        // TODO: Google Ads API get by ID
        return null;
    }

    public function updateAd(AdDomainData $domainDto): AdExternalData
    {
        // TODO: wire into Google Ads mutate logic for update
        throw new \RuntimeException('Not yet implemented');
    }

    public function removeAd(string $id): bool
    {
        // TODO: wire into Google Ads API remove
        return false;
    }
}
