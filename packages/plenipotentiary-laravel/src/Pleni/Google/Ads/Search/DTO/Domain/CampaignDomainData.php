<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain;

use App\Models\Search\Campaign as DbCampaign;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

/**
 * Domain DTO representing local Campaign persisted in our DB.
 */
class CampaignDomainData implements \JsonSerializable
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $resourceName = null,
        public readonly ?float $dailyBudget = null,
        public readonly ?int $campaignId = null,
        public readonly ?string $budgetResourceName = null,
        public readonly ?string $customerId = null,
    ) {}

    public static function fromModel(DbCampaign $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            status: $model->status,
            resourceName: $model->resource_name,
            dailyBudget: $model->daily_budget,
            campaignId: $model->campaign_id,
            budgetResourceName: $model->budget_resource_name,
            customerId: $model->customer_id ?? GoogleAdsConfig::defaultCustomerId(),
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
