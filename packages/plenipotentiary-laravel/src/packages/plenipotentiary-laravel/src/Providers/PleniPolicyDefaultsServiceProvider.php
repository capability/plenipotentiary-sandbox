<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain;
use Plenipotentiary\Laravel\Pleni\Policies\LoggingPolicy;
use Plenipotentiary\Laravel\Pleni\Policies\RetryBackoffPolicy;
use Plenipotentiary\Laravel\Pleni\Policies\RateLimitPolicy;
use Plenipotentiary\Laravel\Pleni\Policies\MetricsPolicy;
use Psr\Log\LoggerInterface;

/**
 * Optional ServiceProvider that binds a default GatewayPolicyChain.
 * Applications may register this for a "batteries-included" policy chain.
 */
final class PleniPolicyDefaultsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GatewayPolicyChain::class, function ($app) {
            return new GatewayPolicyChain([
                new LoggingPolicy($app->make(LoggerInterface::class)),
                new RetryBackoffPolicy(),
                new RateLimitPolicy(),
                new MetricsPolicy(),
            ]);
        });
    }
}
