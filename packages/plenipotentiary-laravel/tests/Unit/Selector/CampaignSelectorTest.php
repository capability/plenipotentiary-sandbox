<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector\CampaignSelector;

it('maps resource name selector to canonical provider context', function () {
    $selector = CampaignSelector::make('resource_name', 'customers/123/campaigns/456', [
        'google.customerId' => '123',
    ]);

    $dto = $selector->toCanonicalDTO();

    expect($dto)->toBeInstanceOf(CampaignCanonicalDTO::class)
        ->and($dto->getProviderContextValue('resourceName'))->toBe('customers/123/campaigns/456')
        ->and($dto->getProviderContextValue('google.customerId'))->toBe('123');
});

it('maps external id selector to canonical external id', function () {
    $selector = CampaignSelector::make('external_id', '789');

    $dto = $selector->toCanonicalDTO();

    expect($dto->externalId)->toBe('789');
});
