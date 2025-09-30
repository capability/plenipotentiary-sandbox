<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update;

use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsResponse;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

final class ResponseMapper
{
    public function toCanonical(MutateCampaignsResponse|MutateGoogleAdsResponse $resp): CampaignCanonicalDTO
    {
        $result = $resp->getResults()[0] ?? null;
        $campaign = $result?->getCampaign();
        $resourceName = $campaign?->getResourceName() ?? $result?->getResourceName();

        return CampaignCanonicalDTO::fromArray([
            'externalId' => $campaign ? (string) $campaign->getId() : null,
            'identifiers' => array_filter([
                'resourceName' => $resourceName,
            ], fn ($value) => $value !== null && $value !== ''),
            'providerContext' => array_filter([
                'resourceName' => $resourceName,
            ], fn ($value) => $value !== null && $value !== ''),
            'name' => $campaign?->getName(),
            'status' => $campaign?->getStatus(),
            'budgetResourceName' => $campaign?->getCampaignBudget(),
        ]);
    }
}
