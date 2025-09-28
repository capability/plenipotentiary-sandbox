<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Endpoint\Gateway;

use Plenipotentiary\Laravel\Contracts\Gateway\ApiEndpointGatewayContract;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiEndpointAdapterContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Pleni\Support\Result;
use Psr\Log\LoggerInterface;

/**
 * eBay Browse API Gateway
 * 
 * Domain layer gateway for eBay Browse API operations.
 * Handles cross-cutting concerns like logging, idempotency, and caching.
 */
final class eBayBrowseGateway implements ApiEndpointGatewayContract
{
    public function __construct(
        private ApiEndpointAdapterContract $adapter,
        private LoggerInterface $logger,
        private IdempotencyStore $idempotencyStore,
        private IdempotencyHints $idempotencyHints,
    ) {}

    public function call(string $operation, array $payload = [], array $options = []): Result
    {
        $this->logger->info("Gateway: call operation", [
            'operation' => $operation,
            'provider' => 'eBay Browse',
            'payload_size' => count($payload),
        ]);

        // Idempotency for state-changing operations
        if ($this->isStateChangingOperation($operation)) {
            $fp = $this->idempotencyHints->fingerprintForCall($operation, $payload);
            $scope = "ebay.{$operation}";

            if ($this->idempotencyStore->isTombstoned($scope, $fp)) {
                return Result::err("Operation {$operation} already tombstoned");
            }

            if ($existing = $this->idempotencyStore->get($scope, $fp)) {
                return Result::ok(json_decode($existing, true));
            }
        }

        $result = $this->adapter->call($operation, $payload, $options);

        // Cache successful state-changing operations
        if ($result->isOk() && $this->isStateChangingOperation($operation)) {
            $fp = $this->idempotencyHints->fingerprintForCall($operation, $payload);
            $scope = "ebay.{$operation}";
            $this->idempotencyStore->put($scope, $fp, json_encode($result->unwrap()));
        }

        return $result;
    }

    public function validate(string $operation, array $payload = []): Result
    {
        $this->logger->info("Gateway: validate operation", [
            'operation' => $operation,
            'provider' => 'eBay Browse',
        ]);

        return $this->adapter->validate($operation, $payload);
    }

    /**
     * Determine if an operation changes state and should be idempotent
     */
    private function isStateChangingOperation(string $operation): bool
    {
        return in_array($operation, [
            'createOffer',
            'updateOffer', 
            'deleteOffer',
            'createListing',
            'updateListing',
            'deleteListing',
        ], true);
    }
}
