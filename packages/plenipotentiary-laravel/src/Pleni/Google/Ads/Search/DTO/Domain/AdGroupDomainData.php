<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain;

use App\Models\Search\AdGroup as DbAdGroup;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

/**
 * Domain DTO representing local AdGroup persisted in our DB.
 */
class AdGroupDomainData implements \JsonSerializable
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $resourceName = null,
        public readonly ?string $campaignResourceName = null,
        public readonly ?float $maxCpc = null,
        public readonly ?string $priceCustomizerLinkResourceName = null,
        public readonly ?string $priceCustomizerText = null,
        public readonly ?string $stockCustomizerLinkResourceName = null,
        public readonly ?string $stockCustomizerText = null,
        public readonly ?string $thumbnailUrl = null,
        public readonly ?string $imageAssetLinkResourceName = null,
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
            priceCustomizerLinkResourceName: $model->price_customizer_link_resource_name,
            priceCustomizerText: $model->price_customizer_text,
            stockCustomizerLinkResourceName: $model->stock_customizer_link_resource_name,
            stockCustomizerText: $model->stock_customizer_text,
            thumbnailUrl: $model->thumbnail_url,
            imageAssetLinkResourceName: $model->image_asset_link_resource_name,
            customerId: $model->customer_id ?? GoogleAdsConfig::defaultCustomerId(),
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
