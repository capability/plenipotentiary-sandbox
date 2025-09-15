<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\CampaignDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\CampaignExternalData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

class CampaignExternalToDomainMapper
{
    public static function toDomain(CampaignExternalData $external): CampaignDomainData
    {
        return new CampaignDomainData(
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
