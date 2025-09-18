<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\DTO;

use App\Models\Search\Ad as DbAd;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

class AdDomainDTO implements \JsonSerializable
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $adId,
        public readonly ?string $resourceName,
        public readonly ?string $status,
        public readonly array $headlines = [],
        public readonly array $descriptions = [],
        public readonly array $finalUrls = [],
        public readonly ?string $path1 = null,
        public readonly ?string $path2 = null,
        public readonly ?string $parentAdGroupResourceName = null,
        public readonly ?string $customerId = null,
    ) {}

    public static function fromModel(DbAd $model): self
    {
        return new self(
            id: $model->id,
            adId: $model->ad_id,
            resourceName: $model->resource_name,
            status: $model->status,
            headlines: [], // Similar logic as old AdDomainData if needed
            descriptions: [],
            finalUrls: ! empty($model->destination_url) ? [$model->destination_url] : [],
            path1: $model->display_url_path1,
            path2: $model->display_url_path2,
            parentAdGroupResourceName: $model->adgroup?->resource_name,
            customerId: $model->customer_id ?? GoogleAdsConfig::defaultCustomerId(),
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
