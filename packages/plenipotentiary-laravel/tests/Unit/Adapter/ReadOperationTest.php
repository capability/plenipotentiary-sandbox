<?php

use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsRequest;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\ReadOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Result;
use Plenipotentiary\Laravel\Contracts\Adapter\OperationContract;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    $this->googleAdsClient = Mockery::mock();
    $this->googleAdsService = Mockery::mock();

    $this->googleAdsClient
        ->shouldReceive('getGoogleAdsServiceClient')
        ->andReturn($this->googleAdsService);

    $this->client = Mockery::mock(ProviderClientContract::class);
    $this->client->shouldReceive('raw')->andReturn($this->googleAdsClient);

    $this->logger = Mockery::mock(LoggerInterface::class);

    $this->operation = new ReadOperation(
        $this->client,
        $this->logger,
    );

    GoogleAdsDefaults::set('google.customerId', null);
});

afterEach(function () {
    Mockery::close();
    GoogleAdsDefaults::set('google.customerId', null);
});

it('implements OperationContract', function () {
    expect($this->operation)->toBeInstanceOf(OperationContract::class);
});

it('reads campaign by external id', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'externalId' => '789',
        'providerContext' => ['google.customerId' => '1234567890'],
    ]);

    $campaign = new Campaign([
        'resource_name' => 'customers/1234567890/campaigns/789',
        'id' => 789,
        'name' => 'Campaign 789',
        'status' => 2,
        'campaign_budget' => 'customers/1234567890/campaignBudgets/456',
    ]);

    $response = new class($campaign)
    {
        public function __construct(private Campaign $campaign) {}

        public function iterateAllElements(): iterable
        {
            yield new class($this->campaign)
            {
                public function __construct(private Campaign $campaign) {}

                public function getCampaign(): Campaign
                {
                    return $this->campaign;
                }
            };
        }
    };

    $this->googleAdsService
        ->shouldReceive('search')
        ->once()
        ->with(Mockery::on(function ($request) {
            return $request instanceof SearchGoogleAdsRequest
                && $request->getCustomerId() === '1234567890'
                && str_contains($request->getQuery(), 'campaign.id = 789');
        }))
        ->andReturn($response);

    $this->logger->shouldReceive('info')->once();

    $result = $this->operation->perform($dto);

    expect($result->isOk())->toBeTrue();

    $canonical = $result->unwrap();
    expect($canonical)->toBeInstanceOf(CampaignCanonicalDTO::class)
        ->and($canonical->externalId)->toBe('789')
        ->and($canonical->getProviderContextValue('resourceName'))->toBe('customers/1234567890/campaigns/789');
});

it('reads campaign by resource name when provided', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'providerContext' => [
            'google.customerId' => '1234567890',
            'resourceName' => 'customers/1234567890/campaigns/999',
        ],
    ]);

    $this->googleAdsService
        ->shouldReceive('search')
        ->once()
        ->with(Mockery::on(function ($request) {
            return $request instanceof SearchGoogleAdsRequest
                && str_contains($request->getQuery(), 'campaign.resource_name = "customers/1234567890/campaigns/999"');
        }))
        ->andReturn(new class {
            public function iterateAllElements(): iterable
            {
                return [];
            }
        });

    $this->logger->shouldReceive('info')->once();

    $result = $this->operation->perform($dto);

    expect($result->isOk())->toBeTrue()
        ->and($result->unwrap())->toBeNull();
});

it('returns invalid when customer id missing', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'externalId' => '789',
    ]);

    $this->googleAdsService->shouldReceive('search')->never();
    $this->logger->shouldReceive('info')->never();

    $result = $this->operation->perform($dto);

    expect($result)->toBeInstanceOf(Result::class)
        ->and($result->isInvalid())->toBeTrue();
});

it('returns invalid when identifiers missing', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'providerContext' => ['google.customerId' => '1234567890'],
    ]);

    $result = $this->operation->perform($dto);

    expect($result->isInvalid())->toBeTrue();
});

it('maps request correctly', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'externalId' => '999',
        'providerContext' => ['google.customerId' => '1234567890'],
    ]);

    $request = (new ReadOperation($this->client, $this->logger))->requestMapper($dto, '1234567890');

    expect($request)->toBeInstanceOf(SearchGoogleAdsRequest::class)
        ->and($request->getCustomerId())->toBe('1234567890')
        ->and($request->getQuery())->toContain('campaign.id = 999');
});

it('maps response correctly', function () {
    $campaign = new Campaign([
        'resource_name' => 'customers/1234567890/campaigns/234',
        'id' => 234,
        'name' => 'Mapped Campaign',
        'status' => 2,
        'campaign_budget' => 'customers/1234567890/campaignBudgets/456',
    ]);

    $response = new class($campaign)
    {
        public function __construct(private Campaign $campaign) {}

        public function iterateAllElements(): iterable
        {
            yield new class($this->campaign)
            {
                public function __construct(private Campaign $campaign) {}
                public function getCampaign(): Campaign { return $this->campaign; }
            };
        }
    };

    $operation = new ReadOperation($this->client, $this->logger);
    $canonical = $operation->responseMapper($response, '1234567890');

    expect($canonical)->toBeInstanceOf(CampaignCanonicalDTO::class)
        ->and($canonical->externalId)->toBe('234')
        ->and($canonical->name)->toBe('Mapped Campaign')
        ->and($canonical->getProviderContextValue('resourceName'))->toBe('customers/1234567890/campaigns/234');
});

describe('ReadOperation::perform validation', function () {
    it('returns invalid when required fields are missing', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            // Missing providerContext.google.customerId
            'externalId' => '999',
        ]);

        $result = $this->operation->perform($dto);

        expect($result->isInvalid())->toBeTrue();
        $violations = $result->violations();
        expect($violations)->toBeArray()
            ->and(array_column($violations, 'field'))->toContain('providerContext.google.customerId');
    });
});
