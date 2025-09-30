<?php

use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignApiCrudGateway;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;
use Plenipotentiary\Laravel\Support\Result;

describe('Campaign API CRUD Gateway', function () {
    beforeEach(function () {
        $this->adapter = Mockery::mock(ApiCrudAdapterContract::class);
        $this->idempotencyStore = Mockery::mock(IdempotencyStore::class);
        $this->idempotencyHints = Mockery::mock(IdempotencyHints::class);
        $this->logger = Mockery::mock(\Psr\Log\LoggerInterface::class);

        $this->gateway = new CampaignApiCrudGateway(
            $this->adapter,
            $this->logger,
            $this->idempotencyStore,
            $this->idempotencyHints
        );
    });

    it('creates campaign with idempotency', function () {
        $dto = $this->createTestCampaignDTO();
        $fingerprint = 'test-fingerprint';
        $expectedResult = Result::ok($dto);

        $this->logger->shouldReceive('info')->once();
        $this->idempotencyHints->shouldReceive('fingerprintForCreate')
            ->with($dto)
            ->andReturn($fingerprint);
        $this->idempotencyStore->shouldReceive('get')
            ->with('campaign.create', $fingerprint)
            ->andReturn(null);
        $this->adapter->shouldReceive('create')
            ->with($dto, false)
            ->andReturn($expectedResult);
        $this->idempotencyStore->shouldReceive('put')
            ->with('campaign.create', $fingerprint, Mockery::type('string'));

        $result = $this->gateway->create($dto);

        expect($result)->toBe($expectedResult);
    });

    it('returns cached result for idempotent create', function () {
        $dto = $this->createTestCampaignDTO();
        $fingerprint = 'test-fingerprint';
        $cachedData = json_encode($dto->toArray());

        $this->logger->shouldReceive('info')->once();
        $this->idempotencyHints->shouldReceive('fingerprintForCreate')
            ->with($dto)
            ->andReturn($fingerprint);
        $this->idempotencyStore->shouldReceive('get')
            ->with('campaign.create', $fingerprint)
            ->andReturn($cachedData);

        $result = $this->gateway->create($dto);

        expect($result->isOk())->toBeTrue()
            ->and($result->unwrap())->toBeInstanceOf(CampaignCanonicalDTO::class);
    });

    it('handles tombstoned operations', function () {
        $dto = $this->createTestCampaignDTO();
        $fingerprint = 'test-fingerprint';

        $this->logger->shouldReceive('info')->once();
        $this->idempotencyHints->shouldReceive('fingerprintForCreate')
            ->with($dto)
            ->andReturn($fingerprint);
        $this->idempotencyStore->shouldReceive('isTombstoned')
            ->with('campaign.create', $fingerprint)
            ->andReturn(true);

        $result = $this->gateway->create($dto);

        expect($result->isErr())->toBeTrue()
            ->and($result->error())->toHaveKey('error', 'Create operation already tombstoned');
    });

    it('finds campaign', function () {
        $selector = CampaignSelector::byExternalId('123', ['google.customerId' => '1234567890']);
        $expectedResult = Result::ok($this->createTestCampaignDTO());

        $this->logger->shouldReceive('info')->once();
        $this->adapter->shouldReceive('find')
            ->with($selector)
            ->andReturn($expectedResult);

        $result = $this->gateway->find($selector);

        expect($result)->toBe($expectedResult);
    });

    it('updates campaign with idempotency', function () {
        $dto = $this->createTestCampaignDTO(['externalId' => '123']);
        $fingerprint = 'update-fingerprint';
        $expectedResult = Result::ok($dto);

        $this->logger->shouldReceive('info')->once();
        $this->idempotencyHints->shouldReceive('fingerprintForUpdate')
            ->with($dto)
            ->andReturn($fingerprint);
        $this->idempotencyStore->shouldReceive('get')
            ->with('campaign.update', $fingerprint)
            ->andReturn(null);
        $this->adapter->shouldReceive('update')
            ->with($dto, false)
            ->andReturn($expectedResult);
        $this->idempotencyStore->shouldReceive('put')
            ->with('campaign.update', $fingerprint, Mockery::type('string'));

        $result = $this->gateway->update($dto);

        expect($result)->toBe($expectedResult);
    });

    it('deletes campaign with tombstoning', function () {
        $selector = CampaignSelector::byExternalId('123', ['google.customerId' => '1234567890']);
        $fingerprint = 'delete-fingerprint';
        $expectedResult = Result::ok();

        $this->logger->shouldReceive('info')->once();
        $this->idempotencyHints->shouldReceive('fingerprintForDelete')
            ->with($selector)
            ->andReturn($fingerprint);
        $this->idempotencyStore->shouldReceive('get')
            ->with('campaign.delete', $fingerprint)
            ->andReturn(null);
        $this->adapter->shouldReceive('delete')
            ->with($selector, false)
            ->andReturn($expectedResult);
        $this->idempotencyStore->shouldReceive('tombstone')
            ->with('campaign.delete', $fingerprint);

        $result = $this->gateway->delete($selector);

        expect($result)->toBe($expectedResult);
    });
});
