<?php

use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignApiCrudAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\DeleteOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\UpdateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

/** @var \Plenipotentiary\Laravel\Tests\Support\TestCase $this */
describe('CampaignApiCrudAdapter', function () {
    beforeEach(function () {
        $this->client = Mockery::mock(ProviderClientContract::class);
        $this->createOperation = Mockery::mock(CreateOperation::class);
        $this->updateOperation = Mockery::mock(UpdateOperation::class);
        $this->deleteOperation = Mockery::mock(DeleteOperation::class);
        $this->errorMapper = Mockery::mock(ErrorMapperContract::class);
        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->adapter = new CampaignApiCrudAdapter(
            $this->client,
            $this->createOperation,
            $this->updateOperation,
            $this->deleteOperation,
            $this->errorMapper,
            $this->logger,
        );
    });

    it('delegates create operations', function () {
        $dto = CampaignCanonicalDTO::fromArray(['name' => 'Summer Campaign']);
        $this->createOperation->shouldReceive('perform')->with($dto, false)->andReturn(Result::ok('created'));

        $result = $this->adapter->create($dto);

        expect($result->unwrap())->toBe('created');
    });

    it('delegates update operations', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'identifiers' => ['resourceName' => 'customers/1/campaigns/2'],
            'name' => 'Edited',
        ]);
        $this->updateOperation->shouldReceive('perform')->with($dto, true)->andReturn(Result::ok('updated'));

        $result = $this->adapter->update($dto, true);

        expect($result->unwrap())->toBe('updated');
    });

    it('delegates delete operations', function () {
        $selector = CampaignSelector::byExternalId('123', ['google.customerId' => '456']);
        $this->deleteOperation->shouldReceive('perform')->with($selector, false)->andReturn(Result::ok('deleted'));

        $result = $this->adapter->delete($selector);

        expect($result->unwrap())->toBe('deleted');
    });

    it('maps errors during find failures', function () {
        $selector = CampaignSelector::byLocalId('1');
        $this->errorMapper->shouldReceive('map')->once()->andReturn(['error' => 'boom']);

        $result = $this->adapter->find($selector);

        expect($result->error())->toMatchArray(['error' => 'boom']);
    });

    it('maps errors during lookup failures', function () {
        $criteria = Mockery::mock(\Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup::class);
        $this->client->shouldReceive('raw')->andThrow(new RuntimeException('bad request'));
        $this->errorMapper->shouldReceive('map')->andReturn(['error' => 'bad request']);

        $result = $this->adapter->lookup($criteria, '123');

        expect($result->error())->toMatchArray(['error' => 'bad request']);
    });
});
