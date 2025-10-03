<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Transfer\Rest;

use Plenipotentiary\Laravel\Contracts\Adapter\RestAdapterContract;
use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Saloon\Http\Request;

/**
 * Google Ads REST gateway.
 *
 * Provides a stable, predictable facade for Google Ads REST operations.
 * Applies cross-cutting concerns before delegating to the adapter.
 */
final class GoogleAdsRestGateway implements RestGatewayContract
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
        $this->logger->info('GoogleAds REST Gateway: Executing request', [
            'request_class' => $request::class,
        ]);

        $call = new GatewayCall(
            'google.ads.rest.'.class_basename($request),
            ['request_class' => $request::class]
        );

        return $this->chain()->invoke(
            fn () => $this->adapter->execute($request),
            $call
        );
    }
}
