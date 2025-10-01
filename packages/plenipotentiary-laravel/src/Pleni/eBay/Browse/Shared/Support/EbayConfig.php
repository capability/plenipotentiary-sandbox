<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support;

/**
 * Centralised configuration loader for eBay Browse.
 * All env() lookups should be done here.
 */
final class EbayConfig
{
    public static function clientId(): string
    {
        return (string) env('EBAY_CLIENT_ID', '');
    }

    public static function clientSecret(): string
    {
        return (string) env('EBAY_CLIENT_SECRET', '');
    }

    public static function refreshToken(): string
    {
        return (string) env('EBAY_REFRESH_TOKEN', '');
    }

    public static function redirectUri(): ?string
    {
        return env('EBAY_REDIRECT_URI') ?: null;
    }

    public static function marketplaceId(): string
    {
        return (string) env('EBAY_MARKETPLACE_ID', 'EBAY_US');
    }

    public static function endUserCtx(): string
    {
        return (string) env('EBAY_ENDUSERCTX', 'affiliateCampaignId=<ePNCampaignId>,affiliateReferenceId=<referenceId>');
    }
}
