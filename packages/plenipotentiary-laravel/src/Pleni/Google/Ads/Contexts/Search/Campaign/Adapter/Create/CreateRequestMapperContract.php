<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create;

use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsRequest;
use Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

interface CreateRequestMapperContract
{
    public function toCampaignsRequest(CampaignCanonicalDTO $c, bool $validateOnly): MutateCampaignsRequest;

    public function toUnifiedRequest(
        CampaignCanonicalDTO $c,
        bool $validateOnly,
        CampaignBudgetOperation $budgetOp
    ): MutateGoogleAdsRequest;
}
