<?php

use Google\Ads\GoogleAds\V21\Services\MutateCampaignResult;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignDelete;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    GoogleAdsDefaults::set('google.customerId', '1234567890');

    $this->client = Mockery::mock(ProviderClientContract::class);
    $this->logger = Mockery::mock(LoggerInterface::class);
    $this->logger->shouldIgnoreMissing();

    $this->operation = new CampaignDelete(
        $this->client,
        $this->logger,
    );
});

it('maps request correctly', function () {
    $dto = CampaignCanonicalDTO::fromArray([
        'externalId' => '555',
        'providerContext' => ['google.customerId' => '1234567890'],
    ]);

    $request = $this->operation->requestMapper($dto, false);

    expect($request)->toBeInstanceOf(MutateCampaignsRequest::class)
        ->and($request->getCustomerId())->toBe('1234567890')
        ->and($request->getOperations())->toHaveCount(1)
        ->and($request->getOperations()[0]->getRemove())->toBe('customers/1234567890/campaigns/555');
});

afterEach(function () {
    Mockery::close();
    GoogleAdsDefaults::set('google.customerId', null);
});

describe('CampaignDelete::requestMapper', function () {
    it('builds remove request when provider context carries resource name', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => [
                'google.customerId' => '1234567890',
                'resourceName' => 'customers/1234567890/campaigns/999',
            ],
        ]);

        $request = $this->operation->requestMapper($dto, true);

        expect($request)->toBeInstanceOf(MutateCampaignsRequest::class)
            ->and($request->getCustomerId())->toBe('1234567890')
            ->and($request->getValidateOnly())->toBeTrue()
            ->and($request->getOperations())->toHaveCount(1)
            ->and($request->getOperations()[0]->getRemove())->toBe('customers/1234567890/campaigns/999');
    });

    it('derives resource name from external id when not provided', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'externalId' => '777',
            'providerContext' => [
                'google.customerId' => '1234567890',
            ],
        ]);

        $request = $this->operation->requestMapper($dto, false);

        expect($request->getOperations()[0]->getRemove())
            ->toBe('customers/1234567890/campaigns/777');
    });

    it('throws when neither externalId nor resourceName provided', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => [
                'google.customerId' => '1234567890',
            ],
        ]);

        expect(fn () => $this->operation->requestMapper($dto))
            ->toThrow(\InvalidArgumentException::class);
    });
});

describe('CampaignDelete::perform validation', function () {
    it('returns invalid when required fields are missing', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            // Missing externalId and providerContext.google.customerId
        ]);

        $result = $this->operation->perform($dto);

        expect($result->isInvalid())->toBeTrue();
        $violations = $result->violations();
        expect($violations)->toBeArray()
            ->and(array_column($violations, 'field'))->toContain('providerContext.google.customerId');
    });
});

describe('CampaignDelete::perform validation', function () {
    it('returns invalid when payload missing customer id', function () {
        GoogleAdsDefaults::set('google.customerId', null);

        $dto = CampaignCanonicalDTO::fromArray([
            'externalId' => '999',
        ]);

        $result = $this->operation->perform($dto);

        expect($result)->toBeInstanceOf(Result::class)
            ->and($result->isInvalid())->toBeTrue();

        GoogleAdsDefaults::set('google.customerId', '1234567890');
    });

    it('returns invalid when missing both resourceName and externalId', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
        ]);

        $result = $this->operation->perform($dto);

        expect($result->isInvalid())->toBeTrue();
    });
});

describe('CampaignDelete::responseMapper', function () {
    it('returns canonical dto with merged provider context', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'externalId' => '777',
            'providerContext' => [
                'google.customerId' => '1234567890',
            ],
        ]);

        $response = new MutateCampaignsResponse([
            'results' => [new MutateCampaignResult([
                'resource_name' => 'customers/1234567890/campaigns/777',
            ])],
        ]);

        $canonical = $this->operation->responseMapper($response, $dto, '1234567890');

        expect($canonical)->toBeInstanceOf(CampaignCanonicalDTO::class)
            ->and($canonical->providerContext)->toHaveKey('google.customerId', '1234567890')
            ->and($canonical->getProviderContextValue('resourceName'))->toBe('customers/1234567890/campaigns/777');
    });
});
