<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\CampaignExternalData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate\CampaignExternalToDomainMapper;

it('maps external campaign to domain correctly', function () {
    $external = new CampaignExternalData(
        resourceName: 'customers/123/campaigns/456',
        campaignId: 456,
        name: 'Test Campaign',
        status: 'ENABLED',
        dailyBudget: 100.0,
        budgetResourceName: 'customers/123/campaignBudgets/1'
    );

    $domain = CampaignExternalToDomainMapper::toDomain($external);

    expect($domain->name)->toBe('Test Campaign')
        ->and($domain->campaignId)->toBe(456)
        ->and($domain->resourceName)->toBe('customers/123/campaigns/456');
});
