<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Google\Ads\GoogleAds\Util\V20\FieldMasks;
use Google\Ads\GoogleAds\V20\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V20\Resources\AdGroup;
use Google\Ads\GoogleAds\V20\Services\AdGroupOperation;
use Google\Ads\GoogleAds\V20\Services\MutateAdGroupsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupDomainData;

class AdGroupRequestBuilder
{
    public static function buildCreate(string $customerId, AdGroupDomainData $dto): MutateAdGroupsRequest
    {
        $adGroup = new AdGroup([
            'name' => $dto->name,
            'status' => $dto->status,
            'campaign' => $dto->campaignResourceName,
            'cpc_bid_micros' => GoogleAdsUtil::toMicros($dto->maxCpc ?? 0),
        ]);

        $operation = new AdGroupOperation;
        $operation->setCreate($adGroup);

        return (new MutateAdGroupsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public static function buildUpdate(string $customerId, AdGroupDomainData $dto): MutateAdGroupsRequest
    {
        $adGroup = new AdGroup([
            'resource_name' => $dto->resourceName,
            'name' => $dto->name,
            'status' => $dto->status,
        ]);

        $operation = new AdGroupOperation;
        $operation->setUpdate($adGroup);
        $operation->setUpdateMask(FieldMasks::fromSet($adGroup));

        return (new MutateAdGroupsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public static function buildRemove(string $customerId, string $resourceName): MutateAdGroupsRequest
    {
        $adGroup = new AdGroup([
            'resource_name' => $resourceName,
            'status' => \Google\Ads\GoogleAds\V20\Enums\AdGroupStatusEnum\AdGroupStatus::REMOVED,
        ]);

        $operation = new AdGroupOperation;
        $operation->setUpdate($adGroup);
        $operation->setUpdateMask(FieldMasks::fromSet($adGroup));

        return (new MutateAdGroupsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }
}
