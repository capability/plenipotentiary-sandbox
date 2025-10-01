<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support;

/**
 * Centralised configuration loader for Google Ads.
 * All env() lookups should be done here.
 */
final class GoogleAdsConfig
{
    public static function clientId(): string
    {
        return (string) env('GOOGLE_ADS_CLIENT_ID', '');
    }

    public static function clientSecret(): string
    {
        return (string) env('GOOGLE_ADS_CLIENT_SECRET', '');
    }

    public static function refreshToken(): string
    {
        return (string) env('GOOGLE_ADS_REFRESH_TOKEN', '');
    }

    public static function developerToken(): string
    {
        return (string) env('GOOGLE_ADS_DEVELOPER_TOKEN', '');
    }

    public static function loginCustomerId(): string
    {
        return (string) env('GOOGLE_ADS_LOGIN_CUSTOMER_ID', '');
    }

    public static function linkedCustomerId(): string
    {
        return (string) env('GOOGLE_ADS_LINKED_CUSTOMER_ID', '');
    }
}
