<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Gateway;

use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Adapter\CrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\CrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Exceptions\DomainException;
use Plenipotentiary\Laravel\Exceptions\DomainInvalidException;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter\TodoCreate;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter\TodoUpdate;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\DTO\TodoCanonicalDTO;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Provider-agnostic gateway for Todo CRUD operations.
 *
 * Demonstrates that CRUD pattern works the same for REST APIs (via Saloon)
 * as it does for SDKs (like Google Ads).
 */
final class TodoCrudGateway implements CrudGatewayContract
{
    use \Plenipotentiary\Laravel\Support\Operation\GatewayPreflightTrait;

    public function __construct(
        private CrudAdapterContract $adapter,
        private LoggerInterface $logger,
        private IdempotencyStore $idempotencyStore,
        private IdempotencyHints $idempotencyHints,
        private ErrorMapperContract $errorMapper,
    ) {}

    private function chain(): GatewayPolicyChain
    {
        return app(GatewayPolicyChain::class);
    }

    public function create(TodoCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        if ($invalid = $this->preflight($this->resolveOperation(TodoCreate::class), $dto)) {
            return $invalid;
        }

        try {
            $call = new GatewayCall('todo.create', $dto->toArray(), ['validateOnly' => $validateOnly]);

            return $this->chain()->invoke(fn () => $this->adapter->create($dto, $validateOnly), $call);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function find(TodoCanonicalDTO $dto): Result
    {
        $this->logger->info('Gateway: find todo', ['id' => $dto->id]);

        try {
            $call = new GatewayCall('todo.find', $dto->toArray());

            return $this->chain()->invoke(fn () => $this->adapter->find($dto), $call);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function list(?int $userId = null): Result
    {
        $this->logger->info('Gateway: list todos', ['userId' => $userId]);

        try {
            $call = new GatewayCall('todo.list', ['userId' => $userId]);

            return $this->chain()->invoke(fn () => $this->adapter->list($userId), $call);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function update(TodoCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        if ($invalid = $this->preflight($this->resolveOperation(TodoUpdate::class), $dto)) {
            return $invalid;
        }

        try {
            $call = new GatewayCall('todo.update', $dto->toArray(), ['validateOnly' => $validateOnly]);

            return $this->chain()->invoke(fn () => $this->adapter->update($dto, $validateOnly), $call);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function delete(TodoCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        $this->logger->info('Gateway: delete todo', ['id' => $dto->id]);

        if ($invalid = $this->preflight($this->adapter, $dto)) {
            return $invalid;
        }

        try {
            $call = new GatewayCall('todo.delete', $dto->toArray(), ['validateOnly' => $validateOnly]);

            return $this->chain()->invoke(fn () => $this->adapter->delete($dto, $validateOnly), $call);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    private function mapException(Throwable $exception): Result
    {
        $mapped = $this->errorMapper->map($exception);

        if ($mapped instanceof DomainInvalidException) {
            return Result::invalid($mapped->violations());
        }

        if ($mapped instanceof DomainException) {
            return Result::err([
                'code' => $mapped->code(),
                'message' => $mapped->getMessage(),
                'httpStatus' => $mapped->httpStatus(),
                'retryable' => $mapped->isRetryable(),
                'meta' => $mapped->meta(),
            ]);
        }

        return Result::err([
            'code' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }

    private function resolveOperation(string $operationClass): AdapterVerbContract
    {
        return $this->adapter instanceof CrudAdapterContract
            ? app($operationClass)
            : throw new \InvalidArgumentException("Adapter does not support resolving operation {$operationClass}");
    }
}
