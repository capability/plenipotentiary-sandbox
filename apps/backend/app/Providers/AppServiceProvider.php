<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Providers\PleniCoreServiceProvider;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Providers\GoogleAdsServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(PleniCoreServiceProvider::class);
        $this->app->register(GoogleAdsServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
