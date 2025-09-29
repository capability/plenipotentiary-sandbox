<?php

namespace Plenipotentiary\Laravel\Tests\Support;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;

if (! function_exists('createTestCampaignDTO')) {
    function createTestCampaignDTO(array $overrides = []): CampaignCanonicalDTO
    {
        $defaults = [
            'accountKeys' => ['google.customerId' => '1234567890'],
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/1234567890/campaignBudgets/123',
            'cpcBidMicros' => 1000000,
        ];

        return CampaignCanonicalDTO::fromArray(array_merge($defaults, $overrides));
    }
}

if (! function_exists('createTestCampaignSelector')) {
    function createTestCampaignSelector(string $value = '123'): CampaignSelector
    {
        return CampaignSelector::byExternalId($value, ['google.customerId' => '1234567890']);
    }
}
