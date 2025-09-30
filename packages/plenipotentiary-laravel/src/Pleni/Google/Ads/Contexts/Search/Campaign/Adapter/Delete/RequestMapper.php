<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete;

use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelectorKind;

final class RequestMapper
{
    public function toDeleteRequest(string $customerId, CampaignSelector $sel, bool $validateOnly = false): MutateCampaignsRequest
    {
        $context = $sel->providerContext();
        $resourceName = $context['resourceName']
            ?? ($sel->type() === CampaignSelectorKind::ResourceName->value
                ? $sel->value()
                : sprintf('customers/%s/campaigns/%s', $customerId, $sel->value()));

        $op = new CampaignOperation;
        $op->setRemove($resourceName);

        return (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$op])
            ->setValidateOnly($validateOnly);
    }
}
