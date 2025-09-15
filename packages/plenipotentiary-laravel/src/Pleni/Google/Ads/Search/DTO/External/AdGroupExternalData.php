<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External;

use Google\Ads\GoogleAds\V20\Enums\AdGroupStatusEnum\AdGroupStatus;
use Google\Ads\GoogleAds\V20\Resources\AdGroup as GoogleAdsAdGroup;

/**
 * External DTO mirroring Google Ads API AdGroup payloads.
 */
class AdGroupExternalData implements \JsonSerializable
{
    public function __construct(
        public readonly ?string $resourceName,
        public readonly ?int $adGroupId,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $campaignResourceName = null,
        public readonly ?float $maxCpc = null,
    ) {}

    public static function fromGoogleResponse(GoogleAdsAdGroup $remote): self
    {
        return new self(
            resourceName: $remote->getResourceName(),
            adGroupId: $remote->getId(),
            name: $remote->getName(),
            status: AdGroupStatus::name($remote->getStatus()),
            campaignResourceName: $remote->getCampaign(),
            maxCpc: $remote->getCpcBidMicros() ? $remote->getCpcBidMicros() / 1e6 : null,
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
