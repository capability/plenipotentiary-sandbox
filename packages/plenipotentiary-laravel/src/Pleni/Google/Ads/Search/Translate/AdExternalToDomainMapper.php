<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\AdExternalData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

/**
 * Mapper to translate Google Ads External DTO into Domain DTO for persistence.
 */
class AdExternalToDomainMapper
{
    public static function toDomain(AdExternalData $external): AdDomainData
    {
        return new AdDomainData(
            id: null,
            adId: $external->adId,
            resourceName: $external->resourceName,
            status: $external->status,
            headlines: $external->headlines,
            descriptions: $external->descriptions,
            finalUrls: $external->finalUrls,
            path1: $external->path1,
            path2: $external->path2,
            parentAdGroupResourceName: $external->parentAdGroupResourceName,
            customerId: GoogleAdsConfig::defaultCustomerId(),
        );
    }
}
