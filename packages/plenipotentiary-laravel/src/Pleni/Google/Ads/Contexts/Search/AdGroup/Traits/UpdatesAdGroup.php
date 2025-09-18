<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Traits;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\DTO\AdGroupDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\AdGroupValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;

trait UpdatesAdGroup
{
    public function update(object $dto): object
    {
        \assert($dto instanceof AdGroupDomainDTO);
        AdGroupValidator::validateForUpdate($dto);

        try {
            return $dto; // placeholder
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error updating AdGroup '{$dto->resourceName}'");
        }
    }
}
