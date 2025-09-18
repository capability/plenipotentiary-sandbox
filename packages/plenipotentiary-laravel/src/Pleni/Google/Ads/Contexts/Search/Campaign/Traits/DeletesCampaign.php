<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Traits;

use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Exceptions\GoogleAdsExceptionMapper;

/**
 * Trait providing the concrete Google Ads SDK call to delete Campaigns.
 */
trait DeletesCampaign
{
    abstract protected function adsClient(): GoogleAdsClient;

    /**
     * Perform the actual API call to delete a campaign.
     */
    protected function doDelete(CampaignDomainDTO $domain): bool
    {
        if (empty($domain->resourceName)) {
            throw new \InvalidArgumentException('Resource name is required for campaign delete');
        }

        try {
            $client = $this->adsClient();
            $service = $client->getCampaignServiceClient();

            $operation = new \Google\Ads\Google\Ads\V20\Services\CampaignOperation();
            $operation->setRemove($domain->resourceName);

            $request = (new \Google\Ads\Google\Ads\V20\Services\MutateCampaignsRequest())
                ->setCustomerId($domain->customerId)
                ->setOperations([$operation]);

            $response = $service->mutateCampaigns($request);

            return $response->getResults()->count() > 0;
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error deleting campaign '{$domain->resourceName}'");
        }
    }
}
