<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\Generated;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\CampaignDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\CampaignExternalData;

/**
 * Generated service with raw Google Ads API calls.
 * This file may be overwritten on regeneration.
 *
 * Responsibilities:
 * - Low-level integration with Google Ads API (raw mutate/search calls).
 * - Should NOT implement contracts directly.
 * - Do NOT add domain/business logic here.
 */
class CampaignService
{
    public function createCampaign(CampaignDomainData $domainDto): CampaignExternalData
    {
        // TODO: wire into Google Ads mutate logic
        throw new \RuntimeException('Not yet implemented');
    }

    public function getCampaign(string $id): ?CampaignExternalData
    {
        // TODO: Google Ads API get by ID
        return null;
    }

    public function updateCampaign(CampaignDomainData $domainDto): CampaignExternalData
    {
        // TODO: wire into Google Ads mutate logic for update
        throw new \RuntimeException('Not yet implemented');
    }

    public function removeCampaign(string $id): bool
    {
        // TODO: wire into Google Ads API remove
        return false;
    }
}
