<?php

use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsResponse;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    GoogleAdsDefaults::set('google.customerId', '1234567890');

    $this->googleAdsClient = Mockery::mock();
    $this->campaignService = Mockery::mock();
    $this->googleAdsService = Mockery::mock();

    $this->googleAdsClient->shouldReceive('getCampaignServiceClient')->andReturn($this->campaignService);
    $this->googleAdsClient->shouldReceive('getGoogleAdsServiceClient')->andReturn($this->googleAdsService);

    $this->client = Mockery::mock(ProviderClientContract::class);
    $this->client->shouldReceive('raw')->andReturn($this->googleAdsClient);

    $this->logger = Mockery::mock(LoggerInterface::class);

    $this->operation = new CreateOperation(
        $this->client,
        $this->logger,
    );
});

afterEach(function () {
    Mockery::close();
    GoogleAdsDefaults::set('google.customerId', null);
});

describe('CreateOperation::perform', function () {
    it('returns ok when validation only and budget resource provided', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/1234567890/campaignBudgets/42',
        ]);

        $this->campaignService
            ->shouldReceive('mutateCampaigns')
            ->once()
            ->with(Mockery::on(fn ($request) => $request instanceof MutateCampaignsRequest
                && $request->getCustomerId() === '1234567890'
                && $request->getValidateOnly() === true))
            ->andReturn(new MutateCampaignsResponse);

        $this->googleAdsService->shouldReceive('mutate')->never();
        $this->logger->shouldReceive('info')->once();

        $result = $this->operation->perform($dto, true);

        expect($result->isOk())->toBeTrue();
    });

    it('dispatches unified mutate when budgetMicros provided', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
            'name' => 'Unified Campaign',
            'status' => 'PAUSED',
            'budgetMicros' => 1_500_000,
        ]);

        $this->googleAdsService
            ->shouldReceive('mutate')
            ->once()
            ->with(Mockery::on(fn ($request) => $request instanceof MutateGoogleAdsRequest
                && $request->getCustomerId() === '1234567890'))
            ->andReturn(new MutateGoogleAdsResponse);

        $this->campaignService->shouldReceive('mutateCampaigns')->never();
        $this->logger->shouldReceive('info')->once();

        $result = $this->operation->perform($dto, true);

        expect($result->isOk())->toBeTrue();
    });

    it('returns invalid result with dev hints when payload incomplete', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'status' => 'ENABLED',
        ]);

        $this->logger->shouldReceive('info')->never();

        $result = $this->operation->perform($dto);

        expect($result->isInvalid())->toBeTrue();
        $violations = $result->violations();
        expect($violations)->not->toBeNull();
        $fields = array_map(fn ($violation) => $violation['field'] ?? null, $violations);
        expect(in_array('_dev', $fields, true))->toBeTrue();
    });
});
