<?php

use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\UpdateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    GoogleAdsDefaults::set('google.customerId', '1234567890');

    $this->client = Mockery::mock(ProviderClientContract::class);
    $this->logger = Mockery::mock(LoggerInterface::class);

    $this->operation = new UpdateOperation(
        $this->client,
        $this->logger,
    );
});

afterEach(function () {
    Mockery::close();
    GoogleAdsDefaults::set('google.customerId', null);
});

describe('UpdateOperation::requestMapper', function () {
    it('builds update request with field mask', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => [
                'google.customerId' => '1234567890',
                'resourceName' => 'customers/1234567890/campaigns/456',
            ],
            'name' => 'Renamed Campaign',
            'status' => 'PAUSED',
        ]);

        $request = $this->operation->requestMapper($dto, true);

        $operation = $request->getOperations()[0];
        $campaign = $operation->getUpdate();
        $mask = $operation->getUpdateMask();

        expect($request)->toBeInstanceOf(MutateCampaignsRequest::class)
            ->and($request->getCustomerId())->toBe('1234567890')
            ->and($request->getValidateOnly())->toBeTrue()
            ->and($campaign->getResourceName())->toBe('customers/1234567890/campaigns/456')
            ->and($campaign->getName())->toBe('Renamed Campaign')
            ->and($campaign->getStatus())->toBe(CampaignStatus::value('PAUSED'))
            ->and($mask->getPaths())->toContain('name', 'status');
    });

    it('throws when resource name missing', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
            'name' => 'Renamed Campaign',
        ]);

        expect(fn () => $this->operation->requestMapper($dto))
            ->toThrow(InvalidArgumentException::class, 'resourceName');
    });

    it('throws when customer id missing', function () {
        GoogleAdsDefaults::set('google.customerId', null);

        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['resourceName' => 'customers/123/campaigns/456'],
            'name' => 'Renamed Campaign',
        ]);

        expect(fn () => $this->operation->requestMapper($dto))
            ->toThrow(InvalidArgumentException::class, 'google.customerId');
    });
});

describe('UpdateOperation::perform validation', function () {
    it('returns invalid when required fields are missing', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            // Missing providerContext.resourceName and providerContext.google.customerId
            'name' => 'Renamed',
        ]);

        $result = $this->operation->perform($dto);

        expect($result->isInvalid())->toBeTrue();

        $violations = $result->violations();
        expect($violations)->toBeArray()
            ->and(array_column($violations, 'field'))->toContain('providerContext.resourceName')
            ->and(array_column($violations, 'field'))->toContain('providerContext.google.customerId');
    });
});

it('maps request correctly', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'providerContext' => [
            'google.customerId' => '1234567890',
            'resourceName' => 'customers/1234567890/campaigns/456',
        ],
        'name' => 'Mapped Update',
        'status' => 'PAUSED',
    ]);

    $request = $this->operation->requestMapper($dto, true);

    expect($request)->toBeInstanceOf(MutateCampaignsRequest::class)
        ->and($request->getCustomerId())->toBe('1234567890')
        ->and($request->getValidateOnly())->toBeTrue()
        ->and($request->getOperations())->toHaveCount(1);
});

it('maps response correctly', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'name' => 'OldName',
        'status' => 'ENABLED',
        'providerContext' => ['google.customerId' => '1234567890', 'resourceName' => 'customers/1234567890/campaigns/456'],
    ]);

    $campaign = new \Google\Ads\GoogleAds\V21\Resources\Campaign([
        'resource_name' => 'customers/1234567890/campaigns/456',
        'id' => 456,
        'name' => 'NewName',
        'status' => 1,
    ]);

    $response = new \Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse([
        'results' => [new \Google\Ads\GoogleAds\V21\Services\MutateCampaignResult([
            'resource_name' => 'customers/1234567890/campaigns/456',
            'campaign' => $campaign,
        ])],
    ]);

    $canonical = $this->operation->responseMapper($response, $dto);

    expect($canonical)->toBeInstanceOf(CampaignCanonicalDTO::class)
        ->and($canonical->name)->toBe('NewName')
        ->and($canonical->externalId)->toBe('456');
});
describe('UpdateOperation::perform validation', function () {
    it('returns invalid when resource name missing', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
            'name' => 'Renamed',
        ]);

        $result = $this->operation->perform($dto);
        $violations = $result->violations();

        expect($result)->toBeInstanceOf(Result::class)
            ->and($result->isInvalid())->toBeTrue();

        expect($violations)->toBeArray()
            ->and($violations[0]['field'])->toBe('providerContext.resourceName');
    });

    it('returns invalid when customerId missing', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['resourceName' => 'customers/123/campaigns/456'],
            'name' => 'Renamed',
        ]);

        $result = $this->operation->perform($dto);
        $violations = $result->violations();

        expect($result)->toBeInstanceOf(Result::class)
            ->and($result->isInvalid())->toBeTrue();

        expect($violations)->toBeArray()
            ->and($violations[0]['field'])->toBe('providerContext.google.customerId');
    });

    it('returns invalid when no mutable fields provided', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => [
                'google.customerId' => '1234567890',
                'resourceName' => 'customers/123/campaigns/456',
            ],
        ]);

        $result = $this->operation->perform($dto);
        $violations = $result->violations();

        expect($result->isInvalid())->toBeTrue();
        expect($violations)->toBeArray()
            ->and($violations[0]['field'])->toBe('(name|status)');
    });
});
