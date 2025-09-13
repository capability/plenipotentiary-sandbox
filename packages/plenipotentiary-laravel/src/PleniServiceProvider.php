<?php

namespace Plenipotentiary\Laravel;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Auth\NoopAuth;
use Plenipotentiary\Laravel\Contracts\AuthStrategy;

class PleniServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package config
        $this->mergeConfigFrom(__DIR__.'/../config/pleni.php', 'pleni');

        // Minimal, testable seam: bind the default AuthStrategy
        $this->app->bind(AuthStrategy::class, function ($app) {
            $default = (string) config('pleni.auth.default', 'noop');

            return match ($default) {
                'noop' => new NoopAuth,
                default => new NoopAuth, // placeholder until real strategies land
            };
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/pleni.php' => config_path('pleni.php'),
        ], 'config');
    }
}
