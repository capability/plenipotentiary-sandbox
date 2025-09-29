<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiEndpointAdapterContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\HttpProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiEndpointGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\EndpointIdempotencyHints;
use Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Adapter\OpenAIAdapter;
use Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Gateway\OpenAIGateway;
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
        $this->app->when(OpenAIAdapter::class)
            ->needs(ErrorMapperContract::class)
            ->give(OpenAIErrorMapper::class);

        // Adapter resolved only when OpenAIGateway asks for it
        $this->app->when(OpenAIGateway::class)
            ->needs(ApiEndpointAdapterContract::class)
            ->give(OpenAIAdapter::class);

        // Idempotency hints for endpoints (provide your implementation)
        $this->app->bind(EndpointIdempotencyHints::class, function () {
            return new class implements EndpointIdempotencyHints {
                public function fingerprintForCall(string $operation, array $payload = [], array $options = []): string
                {
                    return hash('sha256', $operation.'|'.json_encode([$payload, $options]));
                }
            };
        });

        // Gateway binding (contextual or concrete)
        $this->app->bind(ApiEndpointGatewayContract::class, OpenAIGateway::class);
    }
}