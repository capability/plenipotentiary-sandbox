<?php

namespace Plenipotentiary\Laravel;

use Illuminate\Contracts\Container\Container;
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
        $this->app->bind(AuthStrategy::class, function (Container $app): AuthStrategy {
            $raw = config('pleni.auth.default');
            $default = is_string($raw) && $raw !== '' ? $raw : 'noop';

            return match ($default) {
                'noop' => $app->make(NoopAuth::class),
                default => $app->make(NoopAuth::class),
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
