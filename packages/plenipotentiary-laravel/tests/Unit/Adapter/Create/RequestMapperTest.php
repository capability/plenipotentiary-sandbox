<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\RequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;

describe('Create Request Mapper', function () {
    it('maps canonical DTO to Google Ads request', function () {
        $mapper = new RequestMapper();
        $dto = CampaignCanonicalDTO::fromArray([
            'accountKeys' => ['google.customerId' => '1234567890'],
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/1234567890/campaignBudgets/123',
        ]);
        
        $request = $mapper->toRequest($dto, false);
        
        expect($request)->toBeInstanceOf(\Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest::class)
            ->and($request->getCustomerId())->toBe('1234567890')
            ->and($request->getValidateOnly())->toBeFalse()
            ->and($request->getOperations())->toHaveCount(1);
        
        $operation = $request->getOperations()[0];
        $campaign = $operation->getCreate();
        
        expect($campaign->getName())->toBe('Test Campaign')
            ->and($campaign->getStatus())->toBe('ENABLED')
            ->and($campaign->getCampaignBudget())->toBe('customers/1234567890/campaignBudgets/123');
    });

    it('sets validate only flag', function () {
        $mapper = new RequestMapper();
        $dto = CampaignCanonicalDTO::fromArray([
            'accountKeys' => ['google.customerId' => '1234567890'],
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/1234567890/campaignBudgets/123',
        ]);
        
        $request = $mapper->toRequest($dto, true);
        
        expect($request->getValidateOnly())->toBeTrue();
    });
});