<?php

use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\UpdateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;
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

describe('UpdateOperation::spec', function () {
    it('accepts resource name with at least one field', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['resourceName' => 'customers/123/campaigns/456'],
            'name' => 'Renamed',
        ]);

        expect(fn () => $this->operation->spec($dto))->not->toThrow(ValidationException::class);
    });

    it('requires resource name', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Renamed',
        ]);

        expect(fn () => $this->operation->spec($dto))
            ->toThrow(ValidationException::class);
    });

    it('requires at least one mutable field', function () {
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['resourceName' => 'customers/123/campaigns/456'],
        ]);

        expect(fn () => $this->operation->spec($dto))
            ->toThrow(ValidationException::class);
    });
});
