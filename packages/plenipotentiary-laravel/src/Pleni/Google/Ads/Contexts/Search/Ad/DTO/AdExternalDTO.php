<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\DTO;

use Google\Ads\GoogleAds\V20\Resources\Ad as GoogleAdsAd;

class AdExternalDTO implements \JsonSerializable
{
    public function __construct(
        public readonly ?string $resourceName,
        public readonly ?int $adId,
        public readonly ?string $status,
        public readonly array $headlines,
        public readonly array $descriptions,
        public readonly array $finalUrls,
        public readonly ?string $path1,
        public readonly ?string $path2,
        public readonly ?string $parentAdGroupResourceName = null,
    ) {}

    public static function fromGoogleResponse(GoogleAdsAd $googleAd): self
    {
        return new self(
            resourceName: $googleAd->getResourceName(),
            adId: $googleAd->getId(),
            status: 'ENABLED', // status is resolved from AdGroupAd
            headlines: [],
            descriptions: [],
            finalUrls: iterator_to_array($googleAd->getFinalUrls()),
            path1: $googleAd->getResponsiveSearchAd()->getPath1(),
            path2: $googleAd->getResponsiveSearchAd()->getPath2(),
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
