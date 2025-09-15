<?php

namespace Plenipotentiary\Laravel;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\AuthStrategy;
use Plenipotentiary\Laravel\Auth\NoopAuth;
use RuntimeException;

class PleniServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package defaults
        $this->mergeConfigFrom(__DIR__.'/../config/pleni.php', 'pleni');

        // Bind the default AuthStrategy, allowing FQCN in config
        $this->app->scoped(AuthStrategy::class, static function (Container $app): AuthStrategy {
            $raw = config('pleni.auth.default', 'noop');

            $impl = match (true) {
                $raw === 'noop' => NoopAuth::class,
                is_string($raw) && class_exists($raw) => $raw, // allow FQCN
                default => NoopAuth::class,
            };

            $resolved = $app->make($impl);

            if (! $resolved instanceof AuthStrategy) {
                throw new RuntimeException("Configured auth [$impl] does not implement AuthStrategy");
            }

            return $resolved;
        });

        // Do NOT bind concrete-to-concrete, Laravel already resolves concretes.
        // If you need an abstraction, define a contract and bind it:
        // $this->app->bind(EbayBrowseItemService::class, UserEbayBrowseItemService::class);
    }

    public function boot(): void
    {
        // Only publish when the helper exists (Testbench safe)
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../config/pleni.php' => config_path('pleni.php'),
            ], 'config');
        }
    }
}

