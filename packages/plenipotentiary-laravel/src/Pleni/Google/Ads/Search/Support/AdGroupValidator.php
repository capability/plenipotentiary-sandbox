<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupDomainData;

class AdGroupValidator
{
    public static function validateForCreate(AdGroupDomainData $dto): void
    {
        if (empty($dto->name)) {
            throw new ValidationException('AdGroup name is required for creation');
        }
        if (empty($dto->campaignResourceName)) {
            throw new ValidationException('Parent Campaign resourceName is required for AdGroup creation');
        }
        // Customer ID validation moved to Campaign-level services/DTOs
    }

    public static function validateForUpdate(AdGroupDomainData $dto): void
    {
        if (empty($dto->resourceName)) {
            throw new ValidationException('Resource name is required for AdGroup update');
        }
        // Customer ID validation moved to Campaign-level services/DTOs
    }

    public static function validateForDelete(AdGroupDomainData $dto): void
    {
        if (empty($dto->resourceName)) {
            throw new ValidationException('Resource name is required for AdGroup delete');
        }
        // Customer ID validation moved to Campaign-level services/DTOs
    }
}
