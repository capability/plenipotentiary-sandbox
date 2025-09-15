<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupCriterionDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\AdGroupCriterionExternalData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

/**
 * Mapper to translate Google Ads External DTO into Domain DTO for persistence.
 */
class AdGroupCriterionExternalToDomainMapper
{
    public static function toDomain(AdGroupCriterionExternalData $external): AdGroupCriterionDomainData
    {
        return new AdGroupCriterionDomainData(
            id: null,
            keywordText: $external->keywordText ?? '',
            matchType: $external->matchType ?? '',
            status: $external->status ?? '',
            resourceName: $external->resourceName,
            criterionId: $external->criterionId,
            parentAdGroupResourceName: $external->parentAdGroupResourceName,
            customerId: GoogleAdsConfig::defaultCustomerId(),
        );
    }
}
