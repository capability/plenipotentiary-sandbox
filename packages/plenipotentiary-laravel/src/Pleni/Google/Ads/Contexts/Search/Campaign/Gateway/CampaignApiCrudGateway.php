<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway;

use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Exceptions\DomainException;
use Plenipotentiary\Laravel\Exceptions\DomainInvalidException;
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
final class CampaignApiCrudGateway implements ApiCrudGatewayContract
{
    public function __construct(
        private ApiCrudAdapterContract $adapter,
        private LoggerInterface $logger,
        private IdempotencyStore $idempotencyStore,
        private IdempotencyHints $idempotencyHints,
        private ErrorMapperContract $errorMapper,
    ) {}

    public function create(CampaignCanonicalDTO $c, bool $validateOnly = false): Result
    {
        $this->logger->info('Gateway: create campaign', ['name' => $c->name]);

        $fp = $this->idempotencyHints->fingerprintForCreate($c);
        $scope = 'campaign.create';

        if ($this->idempotencyStore->isTombstoned($scope, $fp)) {
            return Result::err('Create operation already tombstoned');
        }

        if ($existing = $this->idempotencyStore->get($scope, $fp)) {
            return Result::ok(CampaignCanonicalDTO::fromArray(json_decode($existing, true)));
        }

        try {
            $result = $this->adapter->create($c, $validateOnly);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }

        if ($result->isOk() && ! $validateOnly) {
            $this->idempotencyStore->put($scope, $fp, json_encode($result->unwrap()->toArray()));
        }

        return $result;
    }

    public function find(CampaignSelector $sel): Result
    {
        $this->logger->info('Gateway: find campaign', ['selector' => $sel->value()]);

        try {
            return $this->adapter->find($sel);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function lookup(Lookup $criteria, string $customerId): Result
    {
        $this->logger->info('Gateway: lookup campaigns', ['customerId' => $customerId]);

        try {
            return $this->adapter->lookup($criteria, $customerId);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }
    }

    public function update(CampaignCanonicalDTO $c, bool $validateOnly = false): Result
    {
        $this->logger->info('Gateway: update campaign', ['id' => $c->externalId]);

        $fp = $this->idempotencyHints->fingerprintForUpdate($c);
        $scope = 'campaign.update';

        if ($this->idempotencyStore->isTombstoned($scope, $fp)) {
            return Result::err('Update operation already tombstoned');
        }

        if ($existing = $this->idempotencyStore->get($scope, $fp)) {
            return Result::ok(CampaignCanonicalDTO::fromArray(json_decode($existing, true)));
        }

        try {
            $result = $this->adapter->update($c, $validateOnly);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }

        if ($result->isOk() && ! $validateOnly) {
            $this->idempotencyStore->put($scope, $fp, json_encode($result->unwrap()->toArray()));
        }

        return $result;
    }

    public function delete(CampaignSelector $sel, bool $validateOnly = false): Result
    {
        $this->logger->info('Gateway: delete campaign', ['selector' => $sel->value()]);

        $fp = $this->idempotencyHints->fingerprintForDelete($sel);
        $scope = 'campaign.delete';

        if ($this->idempotencyStore->isTombstoned($scope, $fp)) {
            return Result::err('Delete operation already tombstoned');
        }

        if ($existing = $this->idempotencyStore->get($scope, $fp)) {
            return Result::ok(CampaignCanonicalDTO::fromArray(json_decode($existing, true)));
        }

        try {
            $result = $this->adapter->delete($sel, $validateOnly);
        } catch (Throwable $exception) {
            return $this->mapException($exception);
        }

        if ($result->isOk() && ! $validateOnly) {
            $this->idempotencyStore->tombstone($scope, $fp);
        }

        return $result;
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
            'code' => $mapped::class,
            'message' => $mapped->getMessage(),
        ]);
    }
}
