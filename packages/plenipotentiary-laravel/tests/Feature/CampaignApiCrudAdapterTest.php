<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\AdapterSupport\CampaignApiInboundDTOMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignInboundDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignOutboundDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Mapper\CampaignOutboundMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignApiCrudGateway;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignApiCrudAdapter;
use Psr\Log\NullLogger;

it('maps a campaign object to inbound dto', function () {
    $campaign = Mockery::mock(\Google\Ads\GoogleAds\V20\Resources\Campaign::class);
    $campaign->shouldReceive('getId')->andReturn(123);
    $campaign->shouldReceive('getName')->andReturn('Test Campaign');
    $campaign->shouldReceive('getStatus')->andReturn('ENABLED');
    $campaign->shouldReceive('getAdvertisingChannelType')->andReturn('SEARCH');
    $campaign->shouldReceive('getResourceName')->andReturn('customers/123/campaigns/456');

    $dto = CampaignApiInboundDTOMapper::fromCampaignObject($campaign);

    expect($dto)->toBeInstanceOf(CampaignInboundDTO::class)
        ->and($dto->getExternalResourceId())->toBe('123')
        ->and($dto->getExternalResourceLabel())->toBe('Test Campaign')
        ->and($dto->getExternalResourceStatus())->toBe('active')
        ->and($dto->getRawResponse())->toHaveKey('resourceName');
});

it('maps outbound dto into array', function () {
    $out = new CampaignOutboundDTO(
        name: 'Outbound Test',
        budgetMicros: 1000000,
        advertisingChannelType: 'SEARCH',
        customerId: '999'
    );

    $mapper = new CampaignOutboundMapper();
    $arr = $mapper->map($out);

    expect($arr)->toHaveKey('name', 'Outbound Test')
        ->and($arr)->toHaveKey('budgetMicros', 1000000)
        ->and($arr)->toHaveKey('customerId', '999');
});

it('delegates gateway calls to adapter', function () {
    $fakeAdapter = Mockery::mock(\Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract::class);
    $fakeDto = new CampaignOutboundDTO('X', 1, 'SEARCH', '123');

    $expectedInbound = new CampaignInboundDTO(['externalResourceId' => '42']);

    $fakeAdapter->shouldReceive('create')->andReturn($expectedInbound);
    $fakeAdapter->shouldReceive('read')->andReturn($expectedInbound);
    $fakeAdapter->shouldReceive('update')->andReturn($expectedInbound);
    $fakeAdapter->shouldReceive('delete')->andReturn($expectedInbound);
    $fakeAdapter->shouldReceive('listAll')->andReturn([$expectedInbound]);

    $gateway = new CampaignApiCrudGateway($fakeAdapter);

    expect($gateway->create($fakeDto))->toBe($expectedInbound)
        ->and($gateway->read($fakeDto))->toBe($expectedInbound)
        ->and($gateway->update($fakeDto))->toBe($expectedInbound)
        ->and($gateway->delete($fakeDto))->toBe($expectedInbound)
        ->and($gateway->listAll())->toBeArray()
        ->and($gateway->listAll()[0])->toBeInstanceOf(CampaignInboundDTO::class);
});
