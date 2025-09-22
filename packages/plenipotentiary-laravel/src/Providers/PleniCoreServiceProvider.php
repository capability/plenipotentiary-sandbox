<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Core ServiceProvider that registers only framework-level bindings.
 * Does not wire any specific provider (Google, Facebook, etc).
 */
final class PleniCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind base-level abstractions here if needed in the future.
        // For now this is intentionally kept minimal, contracts are 
        // implemented only in specific provider ServiceProviders.

        // Short-term idempotency barrier (retry/race protection)
        $this->app->singleton(
            \Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore::class,
            function ($app) {
                return new \Plenipotentiary\Laravel\Idempotency\CacheIdempotencyStore(
                    $app->make('cache')->store(),
                    3600
                );
            }
        );
    }
}
