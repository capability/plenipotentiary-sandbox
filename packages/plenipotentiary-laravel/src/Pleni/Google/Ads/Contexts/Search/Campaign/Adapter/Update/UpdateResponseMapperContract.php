<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update;

use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsResponse;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

interface UpdateResponseMapperContract
{
    /** @param MutateCampaignsResponse|MutateGoogleAdsResponse $resp */
    public function toCanonical(MutateCampaignsResponse|MutateGoogleAdsResponse $resp): CampaignCanonicalDTO;
}
