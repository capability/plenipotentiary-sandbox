<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support;

use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Throwable;

final class GoogleAdsErrorMapper implements ErrorMapperContract
{
    /**
     * Map provider-specific exceptions into your app’s domain-friendly errors
     */
    public function map(Throwable $e): Throwable
    {
        // Example: inspect the exception type and rethrow/translate
        // For now, just rethrow as-is
        return $e;

        // Optionally, wrap Google Ads ApiException to your own
        /*
        if ($e instanceof \Google\Ads\GoogleAds\Lib\V20\Errors\ApiException) {
            return new \DomainException($e->getMessage(), $e->getCode(), $e);
        }
        */
    }
}