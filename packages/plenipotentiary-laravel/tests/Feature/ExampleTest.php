<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignApiCrudGateway;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Support\Result;

it('can resolve the CampaignApiCrudGateway and run validateOnly create', function () {
    $gateway = app(CampaignApiCrudGateway::class);

    $dto = CampaignCanonicalDTO::fromArray([
        'name' => 'Example Campaign',
        'status' => 'ENABLED',
        'providerContext' => ['google.customerId' => '1234567890'],
    ]);

    $result = $gateway->create($dto, true);

    expect($result)->toBeInstanceOf(Result::class)
        ->and($result->isOk())->toBeTrue();
});
