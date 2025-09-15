<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdDomainData;

class AdValidator
{
    public static function validateForCreate(AdDomainData $dto): void
    {
        if (empty($dto->parentAdGroupResourceName)) {
            throw new ValidationException('Parent AdGroup resource name is required for Ad creation');
        }
        // Customer ID validation moved to Campaign-level services/DTOs
    }

    public static function validateForUpdate(AdDomainData $dto): void
    {
        if (empty($dto->resourceName)) {
            throw new ValidationException('Resource name is required for Ad update');
        }
        // Customer ID validation moved to Campaign-level services/DTOs
    }

    public static function validateForDelete(AdDomainData $dto): void
    {
        if (empty($dto->resourceName)) {
            throw new ValidationException('Resource name is required for Ad delete');
        }
        // Customer ID validation moved to Campaign-level services/DTOs
    }
}
