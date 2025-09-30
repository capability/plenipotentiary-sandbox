<?php

use Google\Ads\GoogleAds\V21\Services\MutateCampaignResult;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\DeleteOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    GoogleAdsDefaults::set('google.customerId', '1234567890');

    $this->client = Mockery::mock(ProviderClientContract::class);
    $this->logger = Mockery::mock(LoggerInterface::class);

    $this->operation = new DeleteOperation(
        $this->client,
        $this->logger,
    );
});

afterEach(function () {
    Mockery::close();
    GoogleAdsDefaults::set('google.customerId', null);
});

describe('DeleteOperation::requestMapper', function () {
    it('builds remove request when selector carries resource name', function () {
        $selector = CampaignSelector::make('resource_name', 'customers/1234567890/campaigns/999', [
            'google.customerId' => '1234567890',
        ]);

        $request = $this->operation->requestMapper('1234567890', $selector, true);

        expect($request)->toBeInstanceOf(MutateCampaignsRequest::class)
            ->and($request->getCustomerId())->toBe('1234567890')
            ->and($request->getValidateOnly())->toBeTrue()
            ->and($request->getOperations())->toHaveCount(1)
            ->and($request->getOperations()[0]->getRemove())->toBe('customers/1234567890/campaigns/999');
    });

    it('derives resource name from external id when not provided', function () {
        $selector = CampaignSelector::make('external_id', '777', [
            'google.customerId' => '1234567890',
        ]);

        $request = $this->operation->requestMapper('1234567890', $selector, false);

        expect($request->getOperations()[0]->getRemove())
            ->toBe('customers/1234567890/campaigns/777');
    });
});

describe('DeleteOperation::spec', function () {
    it('accepts selectors with values', function () {
        $selector = CampaignSelector::make('external_id', '777');

        expect(fn () => $this->operation->spec($selector))
            ->not->toThrow(ValidationException::class);
    });

    it('rejects selectors without values', function () {
        $selector = CampaignSelector::make('external_id', '');

        expect(fn () => $this->operation->spec($selector))
            ->toThrow(ValidationException::class);
    });
});

describe('DeleteOperation::responseMapper', function () {
    it('returns canonical dto with merged provider context', function () {
        $selector = CampaignSelector::make('external_id', '777', [
            'google.customerId' => '1234567890',
        ]);

        $response = new MutateCampaignsResponse([
            'results' => [new MutateCampaignResult([
                'resource_name' => 'customers/1234567890/campaigns/777',
            ])],
        ]);

        $canonical = $this->operation->responseMapper($response, $selector, '1234567890', [
            'google.customerId' => '1234567890',
        ]);

        expect($canonical)->toBeInstanceOf(CampaignCanonicalDTO::class)
            ->and($canonical->providerContext)->toHaveKey('google.customerId', '1234567890')
            ->and($canonical->getProviderContextValue('resourceName'))->toBe('customers/1234567890/campaigns/777');
    });
});
