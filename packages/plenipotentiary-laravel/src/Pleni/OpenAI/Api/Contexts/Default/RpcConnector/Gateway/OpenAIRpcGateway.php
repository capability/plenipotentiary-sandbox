<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Contexts\Default\RpcConnector\Gateway;

use Plenipotentiary\Laravel\Contracts\Adapter\RpcAdapterContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiRpcGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\EndpointIdempotencyHints;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class OpenAIRpcGateway implements ApiRpcGatewayContract
{
    public function __construct(
        private RpcAdapterContract $adapter,
        private LoggerInterface $logger,
        private IdempotencyStore $idempotencyStore,
        private EndpointIdempotencyHints $idempotencyHints,
    ) {}

    public function call(string $operation, array $payload = [], array $options = []): Result
    {
        $this->logger->info('Gateway: call operation', [
            'operation' => $operation,
            'provider' => 'OpenAI',
            'model' => $payload['model'] ?? 'unknown',
        ]);

        if ($this->isStateChangingOperation($operation)) {
            $fp = $this->idempotencyHints->fingerprintForCall($operation, $payload, $options);
            $scope = "openai.{$operation}";

            if ($this->idempotencyStore->isTombstoned($scope, $fp)) {
                return Result::err("Operation {$operation} already tombstoned");
            }

            if ($existing = $this->idempotencyStore->get($scope, $fp)) {
                return Result::ok(json_decode($existing, true));
            }
        }

        $result = $this->adapter->call($operation, $payload, $options);

        if ($result->isOk() && $this->isStateChangingOperation($operation)) {
            $fp = $this->idempotencyHints->fingerprintForCall($operation, $payload, $options);
            $scope = "openai.{$operation}";
            $this->idempotencyStore->put($scope, $fp, json_encode($result->unwrap()));
        }

        return $result;
    }

    public function validate(string $operation, array $payload = []): Result
    {
        $this->logger->info('Gateway: validate operation', [
            'operation' => $operation,
            'provider' => 'OpenAI',
        ]);

        return $this->adapter->validate($operation, $payload);
    }

    private function isStateChangingOperation(string $operation): bool
    {
        return in_array($operation, [
            'createFineTune',
            'cancelFineTune',
            'deleteModel',
        ], true);
    }
}
