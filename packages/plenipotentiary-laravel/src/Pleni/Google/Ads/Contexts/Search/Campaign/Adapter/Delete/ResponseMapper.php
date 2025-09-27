<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete;

use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsResponse;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

final class ResponseMapper
{
    /** @param MutateCampaignsResponse|MutateGoogleAdsResponse $resp */
    public function toCanonical(MutateCampaignsResponse|MutateGoogleAdsResponse $resp): CampaignCanonicalDTO
    {
        $result = $resp->getResults()[0] ?? null;
        $c = new CampaignCanonicalDTO();
        if ($result) {
            $c->resourceName = $result->getResourceName();
        }
        return $c;
    }
}
