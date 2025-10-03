<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway;

use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Adapter\CrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Exceptions\DomainException;
use Plenipotentiary\Laravel\Exceptions\DomainInvalidException;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicyChain;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignCreate;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignUpdate;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Provider-agnostic gateway class.
 *
 * Delegates CRUD operations to provider-specific adapters.
 * Central location for logging, events, or job dispatch.
 */
final class CampaignCrudGateway implements ApiCrudGatewayContract
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

    public function create(CampaignCanonicalDTO $c, bool $validateOnly = false): Result
    {
        if ($invalid = $this->preflight($this->resolveOperation(CampaignCreate::class), $c)) {
            return $invalid;
        }

        try {
            $call = new GatewayCall('campaign.create', $c->toArray(), ['validateOnly' => $validateOnly]);

            return $this->chain()->invoke(fn () => $this->adapter->create($c, $validateOnly), $call);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function find(CampaignSelector $sel): Result
    {
        $this->logger->info('Gateway: find campaign', ['selector' => $sel->value()]);

        try {
            $call = new GatewayCall('campaign.find', $sel->toCanonicalDTO()->toArray());

            return $this->chain()->invoke(fn () => $this->adapter->find($sel), $call);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function lookup(Lookup $criteria, string $customerId): Result
    {
        $this->logger->info('Gateway: lookup campaigns', ['customerId' => $customerId]);

        try {
            $call = new GatewayCall('campaign.lookup', ['customerId' => $customerId]);

            return $this->chain()->invoke(fn () => $this->adapter->lookup($criteria, $customerId), $call);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function update(CampaignCanonicalDTO $c, bool $validateOnly = false): Result
    {
        if ($invalid = $this->preflight($this->resolveOperation(CampaignUpdate::class), $c)) {
            return $invalid;
        }

        try {
            $call = new GatewayCall('campaign.update', $c->toArray(), ['validateOnly' => $validateOnly]);

            return $this->chain()->invoke(fn () => $this->adapter->update($c, $validateOnly), $call);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function delete(CampaignSelector $sel, bool $validateOnly = false): Result
    {
        $this->logger->info('Gateway: delete campaign', ['selector' => $sel->value()]);

        if ($invalid = $this->preflight($this->adapter, $sel->toCanonicalDTO())) {
            return $invalid;
        }

        try {
            $call = new GatewayCall('campaign.delete', $sel->toCanonicalDTO()->toArray(), ['validateOnly' => $validateOnly]);

            return $this->chain()->invoke(fn () => $this->adapter->delete($sel, $validateOnly), $call);
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

    /**
     * Resolve a concrete operation instance (Create/Update/Delete/...).
     */
    private function resolveOperation(string $operationClass): AdapterVerbContract
    {
        return $this->adapter instanceof CrudAdapterContract
            ? app($operationClass)
            : throw new \InvalidArgumentException("Adapter does not support resolving operation {$operationClass}");
    }
}
