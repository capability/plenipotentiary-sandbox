<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Contexts\Default\RpcConnector\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\RpcAdapterContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\HttpProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiRpcGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\EndpointIdempotencyHints;
use Plenipotentiary\Laravel\Pleni\OpenAI\Api\Contexts\Default\RpcConnector\Adapter\OpenAIRpcAdapter;
use Plenipotentiary\Laravel\Pleni\OpenAI\Api\Contexts\Default\RpcConnector\Gateway\OpenAIRpcGateway;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Auth\OpenAISdkAuthStrategy;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Auth\OpenAISdkClient;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Support\OpenAIErrorMapper;
use Plenipotentiary\Laravel\Pleni\Policies\LoggingPolicy;
use Plenipotentiary\Laravel\Pleni\Policies\MetricsPolicy;
use Plenipotentiary\Laravel\Pleni\Policies\RateLimitPolicy;
use Plenipotentiary\Laravel\Pleni\Policies\RetryBackoffPolicy;

final class OpenAIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Contextual auth
        $this->app->bind(SdkAuthStrategyContract::class, OpenAISdkAuthStrategy::class);

        // Contextual HTTP client for this adapter
        $this->app->when(OpenAIRpcAdapter::class)
            ->needs(HttpProviderClientContract::class)
            ->give(function ($app) {
                /** @var SdkAuthStrategyContract $auth */
                $auth = $app->make(SdkAuthStrategyContract::class);

                return new OpenAISdkClient($auth->getClient());
            });

        // Contextual error mapper
        $this->app->when(OpenAIRpcAdapter::class)
            ->needs(ErrorMapperContract::class)
            ->give(OpenAIErrorMapper::class);

        // Adapter resolved only when OpenAIRpcGateway asks for it
        $this->app->when(OpenAIRpcGateway::class)
            ->needs(RpcAdapterContract::class)
            ->give(OpenAIRpcAdapter::class);

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
        $this->app->bind(ApiRpcGatewayContract::class, OpenAIRpcGateway::class);

        // Register default policies if none configured
        $this->app->resolving(\Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain::class, function ($chain, $app) {
            if (empty(config('pleni.policies'))) {
                $chain = new \Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain([
                    new LoggingPolicy($app->make(\Psr\Log\LoggerInterface::class)),
                    new RetryBackoffPolicy,
                    new RateLimitPolicy,
                    new MetricsPolicy,
                ]);
            }

            return $chain;
        });
    }
}
