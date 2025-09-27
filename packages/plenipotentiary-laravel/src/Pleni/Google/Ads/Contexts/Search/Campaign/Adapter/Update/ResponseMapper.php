<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update;

use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsResponse;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

final class ResponseMapper
{
    /** @param MutateCampaignsResponse|MutateGoogleAdsResponse $resp */
    public function toCanonical(MutateCampaignsResponse|MutateGoogleAdsResponse $resp): CampaignCanonicalDTO
    {
        $results = $resp->getResults();
        $resource = $results[0]?->getCampaign();
        $c = new CampaignCanonicalDTO();

        if ($resource) {
            $c->resourceName       = $resource->getResourceName();
            $c->id                 = (string) $resource->getId();
            $c->name               = $resource->getName();
            $c->status             = $resource->getStatus();
            $c->budgetResourceName = $resource->getCampaignBudget();
        }

        return $c;
    }
}
