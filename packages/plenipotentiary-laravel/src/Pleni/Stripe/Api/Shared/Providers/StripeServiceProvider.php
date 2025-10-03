<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Auth\StripeRestAuthStrategy;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Support\StripeConfig;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Support\StripeErrorMapper;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Transfer\Rest\StripeApiRestConnector;

/**
 * Service provider for Stripe shared services.
 *
 * This shows how REST-based authentication is wired up in the DI container.
 * Compare this to GoogleAdsServiceProvider to see the SDK vs REST differences.
 */
final class StripeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register auth strategy
        $this->app->singleton(StripeRestAuthStrategy::class, function () {
            return new StripeRestAuthStrategy(
                secretKey: StripeConfig::secretKey()
            );
        });

        // Register REST connector (uses auth strategy)
        $this->app->singleton(StripeApiRestConnector::class, function ($app) {
            return new StripeApiRestConnector(
                authStrategy: $app->make(StripeRestAuthStrategy::class)
            );
        });

        // Register error mapper
        $this->app->singleton(StripeErrorMapper::class);
    }
}
