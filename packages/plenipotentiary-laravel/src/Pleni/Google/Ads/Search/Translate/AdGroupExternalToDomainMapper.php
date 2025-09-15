<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\AdGroupExternalData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

/**
 * Mapper to translate Google Ads AdGroup External DTO into Domain DTO.
 */
class AdGroupExternalToDomainMapper
{
    public static function toDomain(AdGroupExternalData $external): AdGroupDomainData
    {
        return new AdGroupDomainData(
            id: null,
            name: $external->name,
            status: $external->status,
            resourceName: $external->resourceName,
            campaignResourceName: $external->campaignResourceName,
            maxCpc: $external->maxCpc,
            priceCustomizerLinkResourceName: null,
            priceCustomizerText: null,
            stockCustomizerLinkResourceName: null,
            stockCustomizerText: null,
            thumbnailUrl: null,
            imageAssetLinkResourceName: null,
            customerId: GoogleAdsConfig::defaultCustomerId(),
        );
    }
}
