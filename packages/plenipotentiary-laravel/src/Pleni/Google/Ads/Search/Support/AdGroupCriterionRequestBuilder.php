<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Google\Ads\GoogleAds\Util\V20\FieldMasks;
use Google\Ads\GoogleAds\V20\Common\KeywordInfo;
use Google\Ads\GoogleAds\V20\Enums\AdGroupCriterionStatusEnum\AdGroupCriterionStatus;
use Google\Ads\GoogleAds\V20\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V20\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V20\Resources\AdGroupCriterion;
use Google\Ads\GoogleAds\V20\Services\AdGroupCriterionOperation;
use Google\Ads\GoogleAds\V20\Services\MutateAdGroupCriteriaRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupCriterionDomainData;

class AdGroupCriterionRequestBuilder
{
    public static function buildCreate(string $customerId, AdGroupCriterionDomainData $dto): MutateAdGroupCriteriaRequest
    {
        $criterion = new AdGroupCriterion([
            'ad_group' => $dto->parentAdGroupResourceName,
            'status' => AdGroupCriterionStatus::value($dto->status),
            'keyword' => new KeywordInfo([
                'text' => $dto->keywordText,
                'match_type' => KeywordMatchType::value($dto->matchType),
            ]),
        ]);

        $operation = new AdGroupCriterionOperation;
        $operation->setCreate($criterion);

        return (new MutateAdGroupCriteriaRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public static function buildUpdate(string $customerId, AdGroupCriterionDomainData $dto): MutateAdGroupCriteriaRequest
    {
        $criterion = new AdGroupCriterion([
            'resource_name' => $dto->resourceName,
            'status' => AdGroupCriterionStatus::value($dto->status),
        ]);

        $operation = new AdGroupCriterionOperation;
        $operation->setUpdate($criterion);
        $operation->setUpdateMask(FieldMasks::fromSet($criterion));

        return (new MutateAdGroupCriteriaRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public static function buildRemove(string $customerId, string $resourceName): MutateAdGroupCriteriaRequest
    {
        $criterion = new AdGroupCriterion([
            'resource_name' => $resourceName,
            'status' => AdGroupCriterionStatus::REMOVED,
        ]);

        $operation = new AdGroupCriterionOperation;
        $operation->setUpdate($criterion);
        $operation->setUpdateMask(FieldMasks::fromSet($criterion));

        return (new MutateAdGroupCriteriaRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }
}
