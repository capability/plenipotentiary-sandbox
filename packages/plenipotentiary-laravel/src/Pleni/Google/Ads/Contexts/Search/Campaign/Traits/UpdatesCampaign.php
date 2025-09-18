<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Traits;

use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignExternalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Exceptions\GoogleAdsExceptionMapper;

/**
 * Trait providing the concrete Google Ads SDK call to update Campaigns.
 */
trait UpdatesCampaign
{
    abstract protected function adsClient(): GoogleAdsClient;

    /**
     * Perform the actual API call to update a campaign.
     */
    protected function doUpdate(CampaignExternalDTO $external): CampaignDomainDTO
    {
        try {
            $client = $this->adsClient();
            $campaignService = $client->getCampaignServiceClient();

            $operation = new \Google\Ads\Google\Ads\V20\Services\CampaignOperation();
            $campaign = new \Google\Ads\Google\Ads\V20\Resources\Campaign([
                'resource_name' => $external->resourceName,
                'name' => $external->name,
                'status' => $external->status,
            ]);
            $operation->setUpdate($campaign);
            $operation->setUpdateMask(\Google\Ads\Google\Ads\Util\V20\FieldMasks::allSetFieldsOf($campaign));

            $request = (new \Google\Ads\Google\Ads\V20\Services\MutateCampaignsRequest())
                ->setCustomerId($external->customerId)
                ->setOperations([$operation]);

            $response = $campaignService->mutateCampaigns($request);
            $updated = $response->getResults()[0]->getResourceName();

            return new CampaignDomainDTO(
                id: null,
                name: $external->name,
                status: $external->status,
                resourceName: $updated,
                dailyBudget: $external->dailyBudget,
                campaignId: $external->campaignId,
                budgetResourceName: $external->budgetResourceName,
                customerId: $external->customerId,
            );
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error updating campaign '{$external->resourceName}'");
        }
    }
}
