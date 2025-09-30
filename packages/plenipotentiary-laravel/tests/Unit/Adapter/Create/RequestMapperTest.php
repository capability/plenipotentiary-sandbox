<?php

use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\RequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;

describe('Create Request Mapper', function () {
    it('maps canonical DTO to Google Ads request', function () {
        $mapper = new RequestMapper;
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/1234567890/campaignBudgets/123',
        ]);

        $request = $mapper->toCampaignsRequest($dto, false);

        expect($request)->toBeInstanceOf(MutateCampaignsRequest::class)
            ->and($request->getCustomerId())->toBe('1234567890')
            ->and($request->getValidateOnly())->toBeFalse()
            ->and($request->getOperations())->toHaveCount(1);

        $operation = $request->getOperations()[0];
        $campaign = $operation->getCreate();

        expect($campaign->getName())->toBe('Test Campaign')
            ->and($campaign->getStatus())->toBe(CampaignStatus::ENABLED)
            ->and($campaign->getCampaignBudget())->toBe('customers/1234567890/campaignBudgets/123');
    });

    it('sets validate only flag', function () {
        $mapper = new RequestMapper;
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/1234567890/campaignBudgets/123',
        ]);

        $request = $mapper->toCampaignsRequest($dto, true);

        expect($request->getValidateOnly())->toBeTrue();
    });

    it('builds unified request when creating budget', function () {
        $mapper = new RequestMapper;
        $dto = CampaignCanonicalDTO::fromArray([
            'providerContext' => ['google.customerId' => '1234567890'],
            'name' => 'Unified Campaign',
            'status' => 'PAUSED',
        ]);

        $budget = new CampaignBudget([
            'resource_name' => 'customers/1234567890/campaignBudgets/-1',
        ]);
        $budgetOp = (new CampaignBudgetOperation())->setCreate($budget);

        $request = $mapper->toUnifiedRequest($dto, false, $budgetOp);

        expect($request)->toBeInstanceOf(MutateGoogleAdsRequest::class)
            ->and($request->getCustomerId())->toBe('1234567890')
            ->and($request->getValidateOnly())->toBeFalse();

        $operations = iterator_to_array($request->getMutateOperations()->getIterator(), false);

        expect($operations)->toHaveCount(2)
            ->and($operations[0]->getCampaignBudgetOperation())->toBe($budgetOp);

        $campaignOp = $operations[1]->getCampaignOperation();
        $campaign = $campaignOp->getCreate();

        expect($campaign->getName())->toBe('Unified Campaign')
            ->and($campaign->getStatus())->toBe(CampaignStatus::PAUSED)
            ->and($campaign->getCampaignBudget())->toBe('customers/1234567890/campaignBudgets/-1');
    });

    it('throws when customer id is missing', function () {
        $mapper = new RequestMapper;
        GoogleAdsDefaults::set('google.customerId', null);
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'No Customer Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/123/campaignBudgets/456',
        ]);

        expect(fn () => $mapper->toCampaignsRequest($dto, false))
            ->toThrow(\InvalidArgumentException::class);
        GoogleAdsDefaults::set('google.customerId', '1234567890');
    });
});
