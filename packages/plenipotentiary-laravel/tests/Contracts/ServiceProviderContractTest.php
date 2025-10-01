<?php

use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;
use Plenipotentiary\Laravel\Tests\Stubs\Auth\FakeGoogleAdsSdkAuthStrategy;
use Plenipotentiary\Laravel\Contracts\Adapter\OperationContract;

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
        $create = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation::class);
        $update = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\UpdateOperation::class);
        $delete = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\DeleteOperation::class);
        $read = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\ReadOperation::class);
        $readMany = app(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\ReadManyOperation::class);

        expect($create)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation::class)
            ->and($update)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\UpdateOperation::class)
            ->and($delete)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\DeleteOperation::class)
            ->and($read)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\ReadOperation::class)
            ->and($readMany)->toBeInstanceOf(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\ReadManyOperation::class)
            ->and($create)->toBeInstanceOf(OperationContract::class)
            ->and($update)->toBeInstanceOf(OperationContract::class)
            ->and($delete)->toBeInstanceOf(OperationContract::class)
            ->and($read)->toBeInstanceOf(OperationContract::class)
            ->and($readMany)->toBeInstanceOf(OperationContract::class);
    });
});
