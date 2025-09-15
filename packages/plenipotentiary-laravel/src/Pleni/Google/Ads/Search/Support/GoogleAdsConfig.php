<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

/**
 * Reads Google Ads related configuration, e.g. default customerId.
 */
class GoogleAdsConfig
{
    /**
     * Get default customerId from .env/config
     */
    public static function defaultCustomerId(): ?string
    {
        return env('GOOGLE_ADS_CUSTOMER_ID');
    }
}
