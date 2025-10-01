<?php

use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Endpoint\Adapter\eBayBrowseAdapter;
use Plenipotentiary\Laravel\Contracts\Client\HttpProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    $this->client = Mockery::mock(HttpProviderClientContract::class);
    $this->errorMapper = Mockery::mock(ErrorMapperContract::class);
    $this->logger = Mockery::mock(LoggerInterface::class);

    $this->adapter = new eBayBrowseAdapter($this->client, $this->errorMapper, $this->logger);
});

afterEach(function () {
    Mockery::close();
});

it('validates that either q or category_ids is required for searchItems', function () {
    $result = $this->adapter->validate('searchItems', []);
    expect($result->isInvalid())->toBeTrue();
    $violations = $result->violations();
    expect(array_column($violations, 'field'))->toContain('q_or_category_ids');
});

it('validates listingDuration and quantity for createOffer', function () {
    $result = $this->adapter->validate('createOffer', []);
    expect($result->isInvalid())->toBeTrue();
    $violations = $result->violations();
    expect(array_column($violations, 'field'))->toContain('listingDuration')
        ->toContain('quantity');
});
