<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

/**
 * Shared utility methods for Google Ads data conversions.
 */
class GoogleAdsUtil
{
    /**
     * Convert currency amount to micros (1,000,000 micros = 1 unit).
     */
    public static function toMicros(float $amount): int
    {
        return (int) round($amount * 1e6);
    }

    /**
     * Convert micros back to a standard currency amount.
     */
    public static function fromMicros(int $micros): float
    {
        return $micros / 1e6;
    }
}
