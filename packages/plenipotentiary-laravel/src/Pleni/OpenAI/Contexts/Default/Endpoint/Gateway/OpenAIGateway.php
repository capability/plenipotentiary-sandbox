<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Gateway;

use Plenipotentiary\Laravel\Contracts\Gateway\ApiEndpointGatewayContract;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiEndpointAdapterContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Pleni\Support\Result;
use Psr\Log\LoggerInterface;

/**
 * OpenAI API Gateway
 * 
 * Domain layer gateway for OpenAI API operations.
 * Handles cross-cutting concerns like logging, idempotency, and caching.
 */
final class OpenAIGateway implements ApiEndpointGatewayContract
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
            'provider' => 'OpenAI',
            'model' => $payload['model'] ?? 'unknown',
        ]);

        // Idempotency for state-changing operations
        if ($this->isStateChangingOperation($operation)) {
            $fp = $this->idempotencyHints->fingerprintForCall($operation, $payload);
            $scope = "openai.{$operation}";

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
            $scope = "openai.{$operation}";
            $this->idempotencyStore->put($scope, $fp, json_encode($result->unwrap()));
        }

        return $result;
    }

    public function validate(string $operation, array $payload = []): Result
    {
        $this->logger->info("Gateway: validate operation", [
            'operation' => $operation,
            'provider' => 'OpenAI',
        ]);

        return $this->adapter->validate($operation, $payload);
    }

    /**
     * Determine if an operation changes state and should be idempotent
     */
    private function isStateChangingOperation(string $operation): bool
    {
        return in_array($operation, [
            'createFineTune',
            'cancelFineTune',
            'deleteModel',
        ], true);
    }
}
