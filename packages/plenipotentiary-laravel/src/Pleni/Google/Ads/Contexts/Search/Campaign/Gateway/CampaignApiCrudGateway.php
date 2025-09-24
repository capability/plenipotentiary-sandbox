<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway;

use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup;
use Plenipotentiary\Laravel\Pleni\Support\Result;
use Psr\Log\LoggerInterface;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;

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
    ) {}

    public function create(CampaignCanonicalDTO $c, bool $validateOnly = false): Result
    {
        $this->logger->info("Gateway: create campaign", ['name' => $c->name]);

        $fp = $this->idempotencyHints->fingerprintForCreate($c);
        $scope = 'campaign.create';

        if ($this->idempotencyStore->isTombstoned($scope, $fp)) {
            return Result::err('Create operation already tombstoned');
        }

        if ($existing = $this->idempotencyStore->get($scope, $fp)) {
            return Result::ok(CampaignCanonicalDTO::fromArray(json_decode($existing, true)));
        }

        $result = $this->adapter->create($c, $validateOnly);

        if ($result->isOk() && !$validateOnly) {
            $this->idempotencyStore->put($scope, $fp, json_encode($result->unwrap()->toArray()));
        }

        return $result;
    }

    public function find(CampaignSelector $sel): Result
    {
        $this->logger->info("Gateway: find campaign", ['selector' => $sel->value()]);
        return $this->adapter->find($sel);
    }

    public function lookup(Lookup $criteria, string $customerId): Result
    {
        $this->logger->info("Gateway: lookup campaigns", ['customerId' => $customerId]);
        return $this->adapter->lookup($criteria, $customerId);
    }

    public function update(CampaignCanonicalDTO $c, bool $validateOnly = false): Result
    {
        $this->logger->info("Gateway: update campaign", ['id' => $c->externalId]);

        $fp = $this->idempotencyHints->fingerprintForUpdate($c);
        $scope = 'campaign.update';

        if ($this->idempotencyStore->isTombstoned($scope, $fp)) {
            return Result::err('Update operation already tombstoned');
        }

        if ($existing = $this->idempotencyStore->get($scope, $fp)) {
            return Result::ok(CampaignCanonicalDTO::fromArray(json_decode($existing, true)));
        }

        $result = $this->adapter->update($c, $validateOnly);

        if ($result->isOk() && !$validateOnly) {
            $this->idempotencyStore->put($scope, $fp, json_encode($result->unwrap()->toArray()));
        }

        return $result;
    }

    public function delete(CampaignSelector $sel, bool $validateOnly = false): Result
    {
        $this->logger->info("Gateway: delete campaign", ['selector' => $sel->value()]);

        $fp = $this->idempotencyHints->fingerprintForDelete($sel);
        $scope = 'campaign.delete';

        if ($this->idempotencyStore->isTombstoned($scope, $fp)) {
            return Result::err('Delete operation already tombstoned');
        }

        if ($existing = $this->idempotencyStore->get($scope, $fp)) {
            return Result::ok(CampaignCanonicalDTO::fromArray(json_decode($existing, true)));
        }

        $result = $this->adapter->delete($sel, $validateOnly);

        if ($result->isOk() && !$validateOnly) {
            $this->idempotencyStore->tombstone($scope, $fp);
        }

        return $result;
    }
}
