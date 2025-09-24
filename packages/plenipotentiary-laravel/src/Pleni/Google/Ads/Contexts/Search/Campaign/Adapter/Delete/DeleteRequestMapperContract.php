<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete;

use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;

interface DeleteRequestMapperContract
{
    public function toDeleteRequest(string $customerId, CampaignSelector $sel, bool $validateOnly): MutateCampaignsRequest;
}
