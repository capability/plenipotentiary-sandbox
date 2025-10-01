<?php

use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Tests\Stubs\Auth\FakeGoogleAdsSdkAuthStrategy;
use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;

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
        $create = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignCreate::class);
        $update = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignUpdate::class);
        $delete = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignDelete::class);
        $read = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignRead::class);
        $readMany = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignReadMany::class);

        expect($create)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignCreate::class)
            ->and($update)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignUpdate::class)
            ->and($delete)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignDelete::class)
            ->and($read)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignRead::class)
            ->and($readMany)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignReadMany::class)
            ->and($create)->toBeInstanceOf(AdapterVerbContract::class)
            ->and($update)->toBeInstanceOf(AdapterVerbContract::class)
            ->and($delete)->toBeInstanceOf(AdapterVerbContract::class)
            ->and($read)->toBeInstanceOf(AdapterVerbContract::class)
            ->and($readMany)->toBeInstanceOf(AdapterVerbContract::class);
    });
});
