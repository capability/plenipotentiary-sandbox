<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignExternalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

class CampaignExternalToDomainMapper
{
    public static function toDomain(CampaignExternalDTO $external): CampaignDomainDTO
    {
        return new CampaignDomainDTO(
            id: null,
            name: $external->name,
            status: $external->status,
            resourceName: $external->resourceName,
            dailyBudget: $external->dailyBudget,
            campaignId: $external->campaignId,
            budgetResourceName: $external->budgetResourceName,
            customerId: GoogleAdsConfig::defaultCustomerId(),
        );
    }
}
