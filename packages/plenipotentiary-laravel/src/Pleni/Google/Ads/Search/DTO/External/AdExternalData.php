<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External;

use Google\Ads\GoogleAds\V20\Common\AdTextAsset;
use Google\Ads\GoogleAds\V20\Enums\ServedAssetFieldTypeEnum\ServedAssetFieldType;
use Google\Ads\GoogleAds\V20\Resources\Ad as GoogleAdsAd;

/**
 * External DTO representing Google Ads Ad payloads (ResponsiveSearchAd).
 */
class AdExternalData implements \JsonSerializable
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
        $responsive = $googleAd->getResponsiveSearchAd();
        $headlines = [];
        foreach ($responsive->getHeadlines() as $asset) {
            /** @var AdTextAsset $asset */
            $pinnedEnum = $asset->getPinnedField();
            $pinnedName = ServedAssetFieldType::name($pinnedEnum);
            $pinned = in_array($pinnedName, ['UNSPECIFIED', 'UNKNOWN']) ? null : $pinnedName;

            $headlines[] = [
                'text' => $asset->getText(),
                'pinned_position' => $pinned,
            ];
        }
        $descriptions = array_map(fn ($a) => $a->getText(), iterator_to_array($responsive->getDescriptions()));

        return new self(
            resourceName: $googleAd->getResourceName(),
            adId: $googleAd->getId(),
            status: 'ENABLED', // status is on AdGroupAd, not Ad
            headlines: $headlines,
            descriptions: $descriptions,
            finalUrls: iterator_to_array($googleAd->getFinalUrls()),
            path1: $responsive->getPath1(),
            path2: $responsive->getPath2()
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
