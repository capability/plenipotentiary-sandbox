<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete;

use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;

final class RequestMapper
{
    public function toDeleteRequest(string $customerId, CampaignSelector $sel, bool $validateOnly = false): MutateCampaignsRequest
    {
        $op = new CampaignOperation();
        $op->setRemove($sel->toResourceName($customerId));

        return (new MutateCampaignsRequest())
            ->setCustomerId($customerId)
            ->setOperations([$op])
            ->setValidateOnly($validateOnly);
    }
}
