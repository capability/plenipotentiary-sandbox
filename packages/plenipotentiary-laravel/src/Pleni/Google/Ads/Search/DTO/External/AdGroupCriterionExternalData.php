<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External;

use Google\Ads\GoogleAds\V20\Enums\AdGroupCriterionStatusEnum\AdGroupCriterionStatus;
use Google\Ads\GoogleAds\V20\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V20\Resources\AdGroupCriterion as GoogleAdsAdGroupCriterion;

/**
 * External DTO representing Google Ads API AdGroupCriterion payloads.
 */
class AdGroupCriterionExternalData implements \JsonSerializable
{
    public function __construct(
        public readonly ?string $resourceName,
        public readonly ?int $criterionId,
        public readonly ?string $keywordText,
        public readonly ?string $matchType,
        public readonly ?string $status,
        public readonly ?string $parentAdGroupResourceName = null,
    ) {}

    public static function fromGoogleResponse(GoogleAdsAdGroupCriterion $remote): self
    {
        $keyword = $remote->getKeyword();

        return new self(
            resourceName: $remote->getResourceName(),
            criterionId: $remote->getCriterionId(),
            keywordText: $keyword?->getText(),
            matchType: $keyword ? KeywordMatchType::name($keyword->getMatchType()) : null,
            status: AdGroupCriterionStatus::name($remote->getStatus()),
            parentAdGroupResourceName: $remote->getAdGroup()
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
