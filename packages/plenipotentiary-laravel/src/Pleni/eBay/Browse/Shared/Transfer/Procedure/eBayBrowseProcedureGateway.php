<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Transfer\Procedure;

use Plenipotentiary\Laravel\Contracts\Adapter\ProcedureAdapterContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ProcedureGatewayContract;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

/**
 * eBay Browse Procedure/RPC gateway.
 *
 * Provides a stable, predictable facade for eBay Browse RPC-style operations.
 * Applies cross-cutting concerns before delegating to the adapter.
 */
final class eBayBrowseProcedureGateway implements ProcedureGatewayContract
{
    public function __construct(
        private ProcedureAdapterContract $adapter,
        private LoggerInterface $logger,
    ) {}

    private function chain(): GatewayPolicyChain
    {
        return app(GatewayPolicyChain::class);
    }

    /**
     * Execute an RPC-style operation through the gateway.
     */
    public function call(string $operation, array $payload = [], array $options = []): Result
    {
        $this->logger->info('eBay Browse Procedure Gateway: Executing operation', [
            'operation' => $operation,
        ]);

        $call = new GatewayCall(
            'ebay.browse.procedure.'.$operation,
            $payload,
            $options
        );

        return $this->chain()->invoke(
            fn () => $this->adapter->call($operation, $payload, $options),
            $call
        );
    }

    /**
     * Validate an operation without executing it.
     */
    public function validate(string $operation, array $payload = []): Result
    {
        $this->logger->info('eBay Browse Procedure Gateway: Validating operation', [
            'operation' => $operation,
        ]);

        return $this->adapter->validate($operation, $payload);
    }
}
