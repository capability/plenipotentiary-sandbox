<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\RpcConnector\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiRpcAdapterContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\HttpProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiRpcGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\EndpointIdempotencyHints;
use Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\RpcConnector\Adapter\OpenAIApiRpcAdapter;
use Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\RpcConnector\Gateway\OpenAIApiRpcGateway;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Auth\OpenAISdkAuthStrategy;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Auth\OpenAISdkClient;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Support\OpenAIErrorMapper;

final class OpenAIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Contextual auth
        $this->app->bind(SdkAuthStrategyContract::class, OpenAISdkAuthStrategy::class);

        // Contextual HTTP client for this adapter
        $this->app->when(OpenAIAdapter::class)
            ->needs(HttpProviderClientContract::class)
            ->give(function ($app) {
                /** @var SdkAuthStrategyContract $auth */
                $auth = $app->make(SdkAuthStrategyContract::class);

                return new OpenAISdkClient($auth->getClient());
            });

        // Contextual error mapper
        $this->app->when(OpenAIApiRpcAdapter::class)
            ->needs(ErrorMapperContract::class)
            ->give(OpenAIErrorMapper::class);

        // Adapter resolved only when OpenAIGateway asks for it
        $this->app->when(OpenAIApiRpcGateway::class)
            ->needs(ApiRpcAdapterContract::class)
            ->give(OpenAIApiRpcAdapter::class);

        // Idempotency hints for endpoints (provide your implementation)
        $this->app->bind(EndpointIdempotencyHints::class, function () {
            return new class implements EndpointIdempotencyHints
            {
                public function fingerprintForCall(string $operation, array $payload = [], array $options = []): string
                {
                    return hash('sha256', $operation.'|'.json_encode([$payload, $options]));
                }
            };
        });

        // Gateway binding (contextual or concrete)
        $this->app->bind(ApiRpcGatewayContract::class, OpenAIApiRpcGateway::class);
    }
}
