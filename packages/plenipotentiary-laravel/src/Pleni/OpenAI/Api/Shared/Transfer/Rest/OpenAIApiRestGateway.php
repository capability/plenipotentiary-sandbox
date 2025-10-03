<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Shared\Transfer\Rest;

use Plenipotentiary\Laravel\Contracts\Adapter\RestAdapterContract;
use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Saloon\Http\Request;

/**
 * OpenAI API REST gateway.
 *
 * Provides a stable, predictable facade for OpenAI API REST operations.
 * Applies cross-cutting concerns before delegating to the adapter.
 */
final class OpenAIApiRestGateway implements RestGatewayContract
{
    public function __construct(
        private RestAdapterContract $adapter,
        private LoggerInterface $logger,
    ) {}

    private function chain(): GatewayPolicyChain
    {
        return app(GatewayPolicyChain::class);
    }

    /**
     * Execute a Saloon request through the gateway.
     */
    public function execute(Request $request): Result
    {
        $this->logger->info('OpenAI API REST Gateway: Executing request', [
            'request_class' => $request::class,
        ]);

        $call = new GatewayCall(
            'openai.api.rest.'.class_basename($request),
            ['request_class' => $request::class]
        );

        return $this->chain()->invoke(
            fn () => $this->adapter->execute($request),
            $call
        );
    }
}
