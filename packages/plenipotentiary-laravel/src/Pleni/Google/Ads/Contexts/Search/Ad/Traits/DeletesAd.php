<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Ad\Traits;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\AdValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;

trait DeletesAd
{
    public function delete(string|int $id): bool
    {
        try {
            return true; // placeholder
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error deleting Ad '$id'");
        }
    }
}
