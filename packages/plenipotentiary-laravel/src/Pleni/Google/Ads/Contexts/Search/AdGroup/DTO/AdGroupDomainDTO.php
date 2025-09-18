<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\DTO;

use App\Models\Search\AdGroup as DbAdGroup;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

class AdGroupDomainDTO implements \JsonSerializable
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $resourceName = null,
        public readonly ?string $campaignResourceName = null,
        public readonly ?float $maxCpc = null,
        public readonly ?string $customerId = null,
    ) {}

    public static function fromModel(DbAdGroup $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            status: $model->status,
            resourceName: $model->resource_name,
            campaignResourceName: $model->campaign?->resource_name,
            maxCpc: $model->max_cpc,
            customerId: $model->customer_id ?? GoogleAdsConfig::defaultCustomerId(),
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
