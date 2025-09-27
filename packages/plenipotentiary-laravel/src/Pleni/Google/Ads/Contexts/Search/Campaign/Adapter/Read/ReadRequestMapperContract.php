<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Read;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;

interface ReadRequestMapperContract
{
    public function toSelectorQuery(string $customerId, CampaignSelector $sel): string;
}
