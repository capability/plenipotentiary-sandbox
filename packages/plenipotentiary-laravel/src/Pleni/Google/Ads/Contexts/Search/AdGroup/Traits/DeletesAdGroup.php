<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\Traits;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\AdGroup\DTO\AdGroupDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\AdGroupValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;

trait DeletesAdGroup
{
    public function delete(string|int $id): bool
    {
        try {
            return true; // placeholder
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error deleting AdGroup '$id'");
        }
    }
}
