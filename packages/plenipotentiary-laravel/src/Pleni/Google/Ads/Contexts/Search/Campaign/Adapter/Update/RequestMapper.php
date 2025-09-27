<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update;

use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\Util\V21\FieldMasks;
use Google\Ads\GoogleAds\V21\Enums\ResponseContentTypeEnum\ResponseContentType;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

final class RequestMapper
{
    public function toRequest(CampaignCanonicalDTO $c, bool $validateOnly = false): MutateCampaignsRequest
    {
        $campaign = new Campaign([
            'resource_name' => $c->resourceName,
            'name'          => $c->name,
            'status'        => $c->status,
        ]);

        $op = new CampaignOperation();
        $op->setUpdate($campaign);
        $op->setUpdateMask(FieldMasks::allSetFieldsOf($campaign));

        return (new MutateCampaignsRequest())
            ->setCustomerId($c->accountKeys['google.customerId'] ?? '')
            ->setOperations([$op])
            ->setValidateOnly($validateOnly)
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }
}
