<?php

use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignResult;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateSupport\CreateBudgetOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    $this->googleAdsClient = Mockery::mock();
    $this->campaignService = Mockery::mock();

    $this->googleAdsClient
        ->shouldReceive('getCampaignServiceClient')
        ->andReturn($this->campaignService);

    $this->client = Mockery::mock(ProviderClientContract::class);
    $this->client->shouldReceive('raw')->andReturn($this->googleAdsClient);

    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->budgetOperation = Mockery::mock(CreateBudgetOperation::class);

    $this->operation = new CreateOperation(
        $this->client,
        $this->logger,
        $this->budgetOperation,
    );
});

describe('CreateOperation::perform validation', function () {
    it('returns invalid when required fields are missing', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            // missing name and providerContext.google.customerId
            'status' => 'ENABLED',
        ]);

        $result = $this->operation->perform($dto);

        expect($result->isInvalid())->toBeTrue();

        $violations = $result->violations();
        expect($violations)->toBeArray()
            ->and(array_column($violations, 'field'))->toContain('name')
            ->and(array_column($violations, 'field'))->toContain('providerContext.google.customerId');
    });
});

it('maps request correctly', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'name' => 'Mapped Campaign',
        'status' => 'ENABLED',
        'budgetResourceName' => 'customers/1234567890/campaignBudgets/111',
        'providerContext' => ['google.customerId' => '1234567890'],
    ]);

    $request = $this->operation->requestMapper($dto, true);

    expect($request)->toBeInstanceOf(MutateCampaignsRequest::class)
        ->and($request->getCustomerId())->toBe('1234567890')
        ->and($request->getValidateOnly())->toBeTrue()
        ->and($request->getOperations())->toHaveCount(1);
});

it('maps response correctly', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'name' => 'Source Campaign',
        'status' => 'PAUSED',
        'providerContext' => ['google.customerId' => '1234567890'],
    ]);

    $campaign = new Campaign([
        'resource_name' => 'customers/1234567890/campaigns/222',
        'id' => 222,
        'name' => 'Response Campaign',
        'status' => 1,
        'campaign_budget' => 'customers/1234567890/campaignBudgets/333',
    ]);

    $response = new MutateCampaignsResponse([
        'results' => [new MutateCampaignResult([
            'resource_name' => 'customers/1234567890/campaigns/222',
            'campaign' => $campaign,
        ])],
    ]);

    $canonical = $this->operation->responseMapper($response, $dto);

    expect($canonical)->toBeInstanceOf(CampaignCanonicalDTO::class)
        ->and($canonical->externalId)->toBe('222')
        ->and($canonical->name)->toBe('Response Campaign')
        ->and($canonical->budgetResourceName)->toBe('customers/1234567890/campaignBudgets/333')
        ->and($canonical->getProviderContextValue('resourceName'))->toBe('customers/1234567890/campaigns/222');
});

afterEach(function () {
    Mockery::close();
});

describe('CreateOperation::perform', function () {
    it('returns ok in validateOnly mode when an existing budget is supplied', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/1234567890/campaignBudgets/42',
            'providerContext' => ['google.customerId' => '1234567890'],
        ]);

        $this->budgetOperation->shouldReceive('create')->never();

        $this->campaignService
            ->shouldReceive('mutateCampaigns')
            ->once()
            ->with(Mockery::on(fn ($request) => $request instanceof MutateCampaignsRequest
                && $request->getCustomerId() === '1234567890'
                && $request->getValidateOnly() === true
                && count($request->getOperations()) === 1))
            ->andReturn(new MutateCampaignsResponse);

        $this->logger->shouldReceive('info')->once();

        $result = $this->operation->perform($dto, true);

        expect($result->isOk())->toBeTrue();
    });

    it('creates a budget when none supplied and returns canonical dto', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Budgetless Campaign',
            'status' => 'PAUSED',
            'budgetMicros' => 1_500_000,
            'providerContext' => ['google.customerId' => '1234567890'],
        ]);

        $this->budgetOperation
            ->shouldReceive('create')
            ->once()
            ->with($dto, 1_500_000, false)
            ->andReturn('customers/1234567890/campaignBudgets/777');

        $campaign = new Campaign([
            'resource_name' => 'customers/1234567890/campaigns/999',
            'id' => 999,
            'name' => 'Budgetless Campaign',
            'status' => 2,
            'campaign_budget' => 'customers/1234567890/campaignBudgets/777',
        ]);

        $response = new MutateCampaignsResponse([
            'results' => [new MutateCampaignResult([
                'resource_name' => 'customers/1234567890/campaigns/999',
                'campaign' => $campaign,
            ])],
        ]);

        $this->campaignService
            ->shouldReceive('mutateCampaigns')
            ->once()
            ->with(Mockery::on(fn ($request) => $request instanceof MutateCampaignsRequest
                && $request->getCustomerId() === '1234567890'
                && $request->getValidateOnly() === false
                && count($request->getOperations()) === 1))
            ->andReturn($response);

        $this->logger->shouldReceive('info')->once();

        $result = $this->operation->perform($dto, false);

        expect($result->isOk())->toBeTrue();

        $canonical = $result->unwrap();
        expect($canonical)->toBeInstanceOf(CampaignCanonicalDTO::class)
            ->and($canonical->getProviderContextValue('resourceName'))->toBe('customers/1234567890/campaigns/999')
            ->and($canonical->budgetResourceName)->toBe('customers/1234567890/campaignBudgets/777')
            ->and($canonical->providerContext)->toHaveKey('google.customerId', '1234567890');
    });

    it('returns invalid structure when minimum fields missing', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'status' => 'ENABLED',
        ]);

        $this->budgetOperation->shouldReceive('create')->never();
        $this->campaignService->shouldReceive('mutateCampaigns')->never();
        $this->logger->shouldReceive('info')->never();

        $result = $this->operation->perform($dto);

        expect($result->isInvalid())->toBeTrue();
        $violations = $result->violations();
        expect($violations)->not->toBeNull();

        $fields = array_map(static fn ($violation) => $violation['field'] ?? null, $violations);
        expect(in_array('name', $fields, true))->toBeTrue();

        $payload = $result->toArray()['payload'] ?? [];
        expect($payload)->toHaveKey('expected');
    });
});
