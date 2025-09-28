<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Endpoint\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiEndpointGatewayContract;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiEndpointAdapterContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;

use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Endpoint\Gateway\eBayBrowseGateway;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Endpoint\Adapter\eBayBrowseAdapter;
use Plenipotentiary\Laravel\Pleni\eBay\Shared\Auth\eBaySdkAuthStrategy;
use Plenipotentiary\Laravel\Pleni\eBay\Shared\Auth\eBaySdkClient;
use Plenipotentiary\Laravel\Pleni\eBay\Shared\Support\eBayErrorMapper;

/**
 * Registers eBay Browse specific adapters, gateways, and services.
 */
final class eBayBrowseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Auth
        $this->app->singleton(SdkAuthStrategyContract::class, eBaySdkAuthStrategy::class);

        // Bind the unified ProviderClientContract to our eBaySdkClient wrapper
        $this->app->singleton(ProviderClientContract::class, function ($app) {
            /** @var SdkAuthStrategyContract $auth */
            $auth = $app->make(SdkAuthStrategyContract::class);

            return new eBaySdkClient(
                $auth->getClient() // raw eBay client
            );
        });

        // Error Mapper
        $this->app->singleton(ErrorMapperContract::class, eBayErrorMapper::class);

        // Adapters
        $this->app->singleton(ApiEndpointAdapterContract::class, eBayBrowseAdapter::class);

        // Gateways
        $this->app->singleton(ApiEndpointGatewayContract::class, eBayBrowseGateway::class);
    }
}
