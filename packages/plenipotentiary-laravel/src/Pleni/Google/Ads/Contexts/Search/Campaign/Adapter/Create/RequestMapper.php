<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create;

use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Enums\ResponseContentTypeEnum\ResponseContentType;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateRequestMapperContract;

final class RequestMapper implements CreateRequestMapperContract
{
    public function toRequest(CampaignCanonicalDTO $c, bool $validateOnly = false): MutateCampaignsRequest
    {
        // Map canonical → provider request payload
        $campaign = new Campaign([
            'name'            => $c->name,
            'status'          => $c->status,              // backed enum string OK in PHP lib
            'campaign_budget' => $c->budgetResourceName,  // resource name, e.g. customers/{cid}/campaignBudgets/{bid}
        ]);

        $op = (new CampaignOperation())->setCreate($campaign);

        // Build the mutate request, ask for full resource back
        return (new MutateCampaignsRequest())
            ->setCustomerId($c->accountKeys['google.customerId'] ?? '')
            ->setOperations([$op])
            ->setValidateOnly($validateOnly)
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }
}
