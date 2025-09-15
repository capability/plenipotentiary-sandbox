<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\CampaignDomainData;

/**
 * Validates CampaignDomainData before passing to services.
 * Returns true/ValidationResult or throws ValidationException on failure.
 */
class CampaignValidator
{
    /**
     * @throws ValidationException
     */
    public static function validateForCreate(CampaignDomainData $dto): void
    {
        if (empty($dto->name)) {
            throw new ValidationException('Campaign name is required for creation');
        }
        if (empty($dto->budgetResourceName)) {
            throw new ValidationException('Campaign budget resource name is required for creation');
        }
        if (empty($dto->customerId)) {
            throw new ValidationException('Customer ID is required for campaign creation');
        }
    }

    /**
     * @throws ValidationException
     */
    public static function validateForUpdate(CampaignDomainData $dto): void
    {
        if (empty($dto->resourceName)) {
            throw new ValidationException('Resource name is required for campaign update');
        }
        if (empty($dto->customerId)) {
            throw new ValidationException('Customer ID is required for campaign update');
        }
    }

    /**
     * @throws ValidationException
     */
    public static function validateId(string|int $id): void
    {
        if (empty($id)) {
            throw new ValidationException('Campaign ID/resource name is required');
        }
    }

    /**
     * @throws ValidationException
     */
    public static function validateForDelete(CampaignDomainData $dto): void
    {
        if (empty($dto->resourceName)) {
            throw new ValidationException('Resource name is required for campaign delete');
        }
        if (empty($dto->customerId)) {
            throw new ValidationException('Customer ID is required for campaign delete');
        }
    }
}
