<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use Google\Ads\GoogleAds\V20\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\Google\Ads\V20\Resources\Campaign as GoogleAdsCampaign;

/**
 * External DTO representing Google Ads API Campaign payloads.
 */
class CampaignExternalDTO implements \JsonSerializable
{
    public function __construct(
        public readonly ?string $resourceName,
        public readonly ?int $campaignId,
        public readonly string $name,
        public readonly string $status,
        public readonly string $customerId,
        public readonly ?float $dailyBudget = null,
        public readonly ?string $budgetResourceName = null,
    ) {}

    public static function fromGoogleResponse(GoogleAdsCampaign $remote, string $customerId): self
    {
        return new self(
            resourceName: $remote->getResourceName(),
            campaignId: $remote->getId(),
            name: $remote->getName(),
            status: CampaignStatus::name($remote->getStatus()),
            customerId: $customerId,
            dailyBudget: null, // budget comes separately
            budgetResourceName: $remote->getCampaignBudget()
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
