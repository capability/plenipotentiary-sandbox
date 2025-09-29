<?php

use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;

describe('Campaign Flow Integration', function () {
    it('performs complete campaign lifecycle', function () {
        $gateway = app(ApiCrudGatewayContract::class);

        // Create campaign
        $createDto = $this->createTestCampaignDTO([
            'name' => 'Integration Test Campaign',
            'status' => 'ENABLED',
        ]);

        $createResult = $gateway->create($createDto, true); // validate only

        expect($createResult->isOk())->toBeTrue();

        // Find campaign (would need actual external ID in real scenario)
        $selector = CampaignSelector::byExternalId('123', ['google.customerId' => '1234567890']);
        $findResult = $gateway->find($selector);

        // Update campaign
        $updateDto = $this->createTestCampaignDTO([
            'externalId' => '123',
            'name' => 'Updated Campaign Name',
        ]);

        $updateResult = $gateway->update($updateDto, true); // validate only

        expect($updateResult->isOk())->toBeTrue();

        // Delete campaign
        $deleteResult = $gateway->delete($selector, true); // validate only

        expect($deleteResult->isOk())->toBeTrue();
    });
});
