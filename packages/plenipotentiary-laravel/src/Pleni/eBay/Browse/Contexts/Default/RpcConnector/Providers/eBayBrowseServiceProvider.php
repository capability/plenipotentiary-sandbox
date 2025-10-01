<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\RpcConnector\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\RpcAdapterContract;
use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\HttpProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiRpcGatewayContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\RpcConnector\Adapter\EbayBrowseRpcAdapter;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\RpcConnector\Gateway\EbayBrowseRpcGateway;
use Plenipotentiary\Laravel\Pleni\eBay\Shared\Auth\eBaySdkAuthStrategy;
use Plenipotentiary\Laravel\Pleni\eBay\Shared\Auth\eBaySdkClient;
use Plenipotentiary\Laravel\Pleni\eBay\Shared\Support\eBayErrorMapper;
use Plenipotentiary\Laravel\Pleni\Policies\LoggingPolicy;
use Plenipotentiary\Laravel\Pleni\Policies\RetryBackoffPolicy;
use Plenipotentiary\Laravel\Pleni\Policies\RateLimitPolicy;
use Plenipotentiary\Laravel\Pleni\Policies\MetricsPolicy;

/**
 * Registers eBay Browse specific adapters, gateways, and services.
 */
final class EbayBrowseServiceProvider extends ServiceProvider
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
        $this->app->singleton(RpcAdapterContract::class, EbayBrowseRpcAdapter::class);

        // Gateways
        $this->app->singleton(ApiRpcGatewayContract::class, EbayBrowseRpcGateway::class);

        // Register default policies if none configured
        $this->app->resolving(\Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain::class, function ($chain, $app) {
            if (empty(config('pleni.policies'))) {
                $chain = new \Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain([
                    new LoggingPolicy($app->make(\Psr\Log\LoggerInterface::class)),
                    new RetryBackoffPolicy(),
                    new RateLimitPolicy(),
                    new MetricsPolicy(),
                ]);
            }
            return $chain;
        });
    }
}
