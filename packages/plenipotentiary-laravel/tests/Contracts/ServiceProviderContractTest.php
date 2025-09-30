<?php

use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Tests\Stubs\Auth\FakeGoogleAdsSdkAuthStrategy;

describe('Service Provider Contracts', function () {
    it('binds core contracts', function () {
        expect(app(IdempotencyStore::class))->toBeInstanceOf(\Plenipotentiary\Laravel\Idempotency\CacheIdempotencyStore::class);
    });

    it('binds Google Ads specific contracts', function () {
        expect(app(SdkAuthStrategyContract::class))->toBeInstanceOf(FakeGoogleAdsSdkAuthStrategy::class)
            ->and(app(ProviderClientContract::class))->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth\GoogleAdsSdkClient::class)
            ->and(app(ErrorMapperContract::class))->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsErrorMapper::class)
            ->and(app(ApiCrudAdapterContract::class))->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignApiCrudAdapter::class)
            ->and(app(ApiCrudGatewayContract::class))->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignApiCrudGateway::class);
    });

    it('binds repository contracts', function () {
        expect(app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryContract::class))
            ->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\EloquentCampaignRepository::class);
    });

    it('binds adapter operations', function () {
        expect(app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation::class))
            ->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation::class)
            ->and(app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\UpdateOperation::class))
            ->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\UpdateOperation::class)
            ->and(app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\DeleteOperation::class))
            ->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\DeleteOperation::class);
    });
});
