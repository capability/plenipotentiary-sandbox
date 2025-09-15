<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain;

use App\Models\Search\Ad as DbAd;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

/**
 * Domain DTO representing local Ad persisted in our DB.
 */
class AdDomainData implements \JsonSerializable
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
        $headlines = [];
        if (! empty($model->pinned_headline1)) {
            $headlines[] = ['text' => $model->pinned_headline1, 'pinned_position' => 'HEADLINE_1'];
        }
        if (! empty($model->pinned_headline2)) {
            $headlines[] = ['text' => $model->pinned_headline2, 'pinned_position' => 'HEADLINE_2'];
        }
        for ($i = 3; $i <= 14; $i++) {
            $key = 'headline'.$i;
            if (! empty($model->$key)) {
                $headlines[] = ['text' => $model->$key, 'pinned_position' => null];
            }
        }

        $descriptions = array_filter([
            $model->description1,
            $model->description2,
            $model->description3,
            $model->description4,
        ], fn ($d) => ! empty($d));

        return new self(
            id: $model->id,
            adId: $model->ad_id,
            resourceName: $model->resource_name,
            status: $model->status,
            headlines: $headlines,
            descriptions: $descriptions,
            finalUrls: ! empty($model->destination_url) ? [$model->destination_url] : [],
            path1: $model->display_url_path1,
            path2: $model->display_url_path2,
            parentAdGroupResourceName: $model->adGroup?->resource_name,
            customerId: $model->customer_id ?? GoogleAdsConfig::defaultCustomerId(),
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
