<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiEndpointGatewayContract;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiEndpointAdapterContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;

use Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Gateway\OpenAIGateway;
use Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Adapter\OpenAIAdapter;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Auth\OpenAISdkAuthStrategy;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Auth\OpenAISdkClient;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Support\OpenAIErrorMapper;

/**
 * Registers OpenAI specific adapters, gateways, and services.
 */
final class OpenAIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Auth
        $this->app->singleton(SdkAuthStrategyContract::class, OpenAISdkAuthStrategy::class);

        // Bind the unified ProviderClientContract to our OpenAISdkClient wrapper
        $this->app->singleton(ProviderClientContract::class, function ($app) {
            /** @var SdkAuthStrategyContract $auth */
            $auth = $app->make(SdkAuthStrategyContract::class);

            return new OpenAISdkClient(
                $auth->getClient() // raw OpenAI client
            );
        });

        // Error Mapper
        $this->app->singleton(ErrorMapperContract::class, OpenAIErrorMapper::class);

        // Adapters
        $this->app->singleton(ApiEndpointAdapterContract::class, OpenAIAdapter::class);

        // Gateways
        $this->app->singleton(ApiEndpointGatewayContract::class, OpenAIGateway::class);
    }
}
