<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain;

use App\Models\Search\AdGroupCriterion as DbAdGroupCriterion;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsConfig;

/**
 * Domain DTO representing local AdGroupCriterion persisted in our DB.
 */
class AdGroupCriterionDomainData implements \JsonSerializable
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $keywordText,
        public readonly string $matchType,
        public readonly string $status,
        public readonly ?string $resourceName = null,
        public readonly ?int $criterionId = null,
        public readonly ?string $parentAdGroupResourceName = null,
        public readonly ?string $customerId = null,
    ) {}

    public static function fromModel(DbAdGroupCriterion $model): self
    {
        return new self(
            id: $model->id,
            keywordText: $model->text,
            matchType: $model->type,
            status: $model->status,
            resourceName: $model->resource_name,
            criterionId: $model->criterion_id,
            parentAdGroupResourceName: $model->adgroup?->resource_name,
            customerId: $model->customer_id ?? GoogleAdsConfig::defaultCustomerId(),
        );
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
