<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\RpcConnector\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiRpcAdapterContract;
use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\HttpProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiRpcGatewayContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\RpcConnector\Adapter\EbayBrowseApiRpcAdapter;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\RpcConnector\Gateway\EbayBrowseApiRpcGateway;
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

        // Bind the unified HttpProviderClientContract to our eBaySdkClient wrapper
        $this->app->singleton(HttpProviderClientContract::class, function ($app) {
            /** @var SdkAuthStrategyContract $auth */
            $auth = $app->make(SdkAuthStrategyContract::class);

            return new eBaySdkClient(
                $auth->getClient() // raw eBay Browse client
            );
        });

        // Error Mapper
        $this->app->singleton(ErrorMapperContract::class, eBayErrorMapper::class);

        // Adapters
        $this->app->singleton(ApiRpcAdapterContract::class, EbayBrowseApiRpcAdapter::class);

        // Gateways
        $this->app->singleton(ApiRpcGatewayContract::class, EbayBrowseApiRpcGateway::class);
    }
}
