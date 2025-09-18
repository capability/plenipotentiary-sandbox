<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use App\Models\Search\Campaign as DbCampaign;

/**
 * Domain DTO representing a Campaign in our system (persisted in DB).
 */
class CampaignDomainDTO implements \JsonSerializable
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $status,
        public readonly string $customerId,
        public readonly ?string $resourceName = null,
        public readonly ?float $dailyBudget = null,
        public readonly ?int $campaignId = null,
        public readonly ?string $budgetResourceName = null,
    ) {}

    public static function fromModel(DbCampaign $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            status: $model->status,
            customerId: $model->customer_id,
            resourceName: $model->resource_name,
            dailyBudget: $model->daily_budget,
            campaignId: $model->campaign_id,
            budgetResourceName: $model->budget_resource_name,
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
