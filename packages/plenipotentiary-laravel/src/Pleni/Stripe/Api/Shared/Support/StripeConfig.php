<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Support;

/**
 * Centralized configuration loader for Stripe.
 * All env() lookups should be done here.
 */
final class StripeConfig
{
    public static function secretKey(): string
    {
        return (string) env('STRIPE_SECRET', '');
    }

    public static function publishableKey(): string
    {
        return (string) env('STRIPE_KEY', '');
    }

    public static function webhookSecret(): ?string
    {
        $secret = env('STRIPE_WEBHOOK_SECRET', '');

        return $secret !== '' ? $secret : null;
    }

    public static function apiVersion(): string
    {
        return (string) env('STRIPE_API_VERSION', '2024-10-28.acacia');
    }
}
