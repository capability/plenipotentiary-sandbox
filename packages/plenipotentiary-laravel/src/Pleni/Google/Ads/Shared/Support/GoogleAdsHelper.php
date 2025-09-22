<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support;

use Google\Ads\GoogleAds\V20\Enums\CampaignStatusEnum\CampaignStatus;

/**
 * Domain-focused service for common Google Ads SDK operations.
 *
 * Encapsulates common conversions and config helpers.
 */
final class GoogleAdsHelper
{
    /**
     * Convert currency amount to micros (1,000,000 micros = 1 unit).
     */
    public static function toMicros(float $amount): int
    {
        return (int) round($amount * 1e6);
    }

    /**
     * Convert micros back to currency amount.
     */
    public static function fromMicros(int $micros): float
    {
        return $micros / 1e6;
    }

    /**
     * Get default campaign config with type hints.
     *
     * Reads values from config with sensible defaults.
     */
    public static function defaultCampaignConfig(): array
    {
        return [
            'budget_amount' => (float) config('googleads.default_campaign_budget', 1.00),
            'status' => CampaignStatus::value(config('googleads.default_campaign_status', 'PAUSED')),
        ];
    }
    /**
     * Fetch the configured Login Customer ID (MCC) for Google Ads.
     *
     * @return string
     */
    public static function loginCustomerId(): string
    {
        return (string) env('GOOGLE_ADS_LOGIN_CUSTOMER_ID', '');
    }

    /**
     * Fetch the configured Linked Customer ID (child account) for Google Ads.
     *
     * @return string
     */
    public static function linkedCustomerId(): string
    {
        return (string) env('GOOGLE_ADS_LINKED_CUSTOMER_ID', '');
    }
}
