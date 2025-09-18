<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Traits;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\DTO\AdDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\AdValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;

trait UpdatesAd
{
    public function update(object $dto): object
    {
        \assert($dto instanceof AdDomainDTO);
        AdValidator::validateForUpdate($dto);

        try {
            return $dto; // placeholder for API update
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error updating Ad '{$dto->resourceName}'");
        }
    }
}
