<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Google\Ads\GoogleAds\Util\V20\FieldMasks;
use Google\Ads\GoogleAds\V20\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V20\Resources\Campaign;
use Google\Ads\GoogleAds\V20\Services\CampaignOperation;
use Google\Ads\GoogleAds\V20\Services\MutateCampaignsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\CampaignDomainData;

/**
 * Builder utility for Campaign mutate requests.
 */
class CampaignRequestBuilder
{
    public static function buildCreate(string $customerId, CampaignDomainData $dto): MutateCampaignsRequest
    {
        $campaign = new Campaign([
            'name' => $dto->name,
            'status' => $dto->status,
            'campaign_budget' => $dto->budgetResourceName,
        ]);

        $operation = new CampaignOperation;
        $operation->setCreate($campaign);

        return (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public static function buildUpdate(string $customerId, CampaignDomainData $dto): MutateCampaignsRequest
    {
        $campaign = new Campaign([
            'resource_name' => $dto->resourceName,
            'name' => $dto->name,
            'status' => $dto->status,
        ]);

        $operation = new CampaignOperation;
        $operation->setUpdate($campaign);
        $operation->setUpdateMask(FieldMasks::fromSet($campaign));

        return (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public static function buildRemove(string $customerId, string $resourceName): MutateCampaignsRequest
    {
        $campaign = new Campaign([
            'resource_name' => $resourceName,
            'status' => \Google\Ads\GoogleAds\V20\Enums\CampaignStatusEnum\CampaignStatus::REMOVED,
        ]);

        $operation = new CampaignOperation;
        $operation->setUpdate($campaign);
        $operation->setUpdateMask(FieldMasks::fromSet($campaign));

        return (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }
}
