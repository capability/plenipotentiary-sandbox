<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupCriterionDomainData;

class AdGroupCriterionValidator
{
    public static function validateForCreate(AdGroupCriterionDomainData $dto): void
    {
        if (empty($dto->keywordText)) {
            throw new ValidationException('Keyword text is required for criterion creation');
        }
        if (empty($dto->matchType)) {
            throw new ValidationException('Match type is required for criterion creation');
        }
        if (empty($dto->parentAdGroupResourceName)) {
            throw new ValidationException('Parent AdGroup resourceName is required for criterion creation');
        }
    }

    public static function validateForUpdate(AdGroupCriterionDomainData $dto): void
    {
        if (empty($dto->resourceName)) {
            throw new ValidationException('Resource name is required for criterion update');
        }
    }

    public static function validateForDelete(AdGroupCriterionDomainData $dto): void
    {
        if (empty($dto->resourceName)) {
            throw new ValidationException('Resource name is required for criterion delete');
        }
    }
}
