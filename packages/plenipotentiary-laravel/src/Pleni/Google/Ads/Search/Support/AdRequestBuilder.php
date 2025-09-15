<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Google\Ads\GoogleAds\Util\V20\FieldMasks;
use Google\Ads\GoogleAds\V20\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V20\Resources\AdGroupAd;
use Google\Ads\GoogleAds\V20\Services\AdGroupAdOperation;
use Google\Ads\GoogleAds\V20\Services\MutateAdGroupAdsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdDomainData;

class AdRequestBuilder
{
    public static function buildCreate(string $customerId, AdGroupAd $adGroupAd): MutateAdGroupAdsRequest
    {
        $operation = new AdGroupAdOperation;
        $operation->setCreate($adGroupAd);

        return (new MutateAdGroupAdsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public static function buildUpdate(string $customerId, AdDomainData $dto, AdGroupAd $adGroupAd): MutateAdGroupAdsRequest
    {
        $operation = new AdGroupAdOperation;
        $operation->setUpdate($adGroupAd);
        $operation->setUpdateMask(FieldMasks::fromSet($adGroupAd));

        return (new MutateAdGroupAdsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public static function buildRemove(string $customerId, string $resourceName): MutateAdGroupAdsRequest
    {
        $adGroupAd = new AdGroupAd([
            'resource_name' => $resourceName,
            'status' => \Google\Ads\GoogleAds\V20\Enums\AdGroupAdStatusEnum\AdGroupAdStatus::REMOVED,
        ]);

        $operation = new AdGroupAdOperation;
        $operation->setUpdate($adGroupAd);
        $operation->setUpdateMask(FieldMasks::fromSet($adGroupAd));

        return (new MutateAdGroupAdsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }
}
