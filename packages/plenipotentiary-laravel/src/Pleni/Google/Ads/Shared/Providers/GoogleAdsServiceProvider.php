<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth\GoogleAdsSdkAuthStrategy;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth\GoogleAdsSdkClient;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsErrorMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Providers\CampaignServiceProvider;

/**
 * Registers Google Ads specific adapters, mappers, and services.
 */
final class GoogleAdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        GoogleAdsDefaults::loadFromEnv();
        // Auth
        $this->app->singleton(SdkAuthStrategyContract::class, GoogleAdsSdkAuthStrategy::class);

        // Bind the unified ProviderClientContract to our GoogleAdsSdkClient wrapper
        $this->app->singleton(ProviderClientContract::class, function ($app) {
            /** @var SdkAuthStrategyContract $auth */
            $auth = $app->make(SdkAuthStrategyContract::class);

            return new GoogleAdsSdkClient(
                $auth->getClient() // raw GoogleAdsClient
            );
        });

        // Error Mapper
        $this->app->singleton(ErrorMapperContract::class, GoogleAdsErrorMapper::class);

        // Resource providers
        $this->app->register(CampaignServiceProvider::class);

    }
}
