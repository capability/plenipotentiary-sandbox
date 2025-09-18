<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Traits;

use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignExternalDTO;

/**
 * Trait providing the concrete Google Ads SDK call to create a Campaign.
 */
trait CreatesCampaign
{
    abstract protected function adsClient(): GoogleAdsClient;
    abstract protected function customerId(): string|int;

    /**
     * Perform the actual API call to create a campaign.
     */
    protected function doCreate(CampaignExternalDTO $external): CampaignDomainDTO
    {
        $client = $this->adsClient();
        $customerId = (string) $this->customerId();

        // For v1, we simply invoke mutateCampaigns in a minimal example.
        $campaignService = $client->getCampaignServiceClient();

        $operation = new \Google\Ads\GoogleAds\V20\Services\CampaignOperation();
        $campaign = new \Google\Ads\GoogleAds\V20\Resources\Campaign([
            'name' => $external->name,
            'status' => $external->status,
            'campaign_budget' => $external->budgetResourceName,
        ]);
        $operation->setCreate($campaign);

        $request = (new \Google\Ads\GoogleAds\V20\Services\MutateCampaignsRequest())
            ->setCustomerId($customerId)
            ->setOperations([$operation]);

        $response = $campaignService->mutateCampaigns($request);
        $created = $response->getResults()[0]->getResourceName();

        // Return as DomainDTO, mapping only basic fields for v1
        return new CampaignDomainDTO(
            id: null,
            name: $external->name,
            status: $external->status,
            resourceName: $created,
            dailyBudget: $external->dailyBudget,
            campaignId: $external->campaignId,
            budgetResourceName: $external->budgetResourceName,
            customerId: (string) $customerId,
        );
    }
}
