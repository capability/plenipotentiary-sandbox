<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Google\Ads\GoogleAds\V20\Services\SearchGoogleAdsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\CampaignExternalData;

class CampaignFinder
{
    /**
     * Find a campaign by its exact name.
     */
    public static function findByName(\Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient $client, string $customerId, string $name): ?CampaignExternalData
    {
        $query = sprintf(
            "SELECT campaign.id, campaign.name, campaign.status, campaign.resource_name, campaign.campaign_budget
             FROM campaign
             WHERE campaign.name = '%s'
             LIMIT 1",
            addslashes($name)
        );

        $request = new SearchGoogleAdsRequest;
        $request->setCustomerId($customerId);
        $request->setQuery($query);

        $response = $client->getGoogleAdsServiceClient()->search($request);

        foreach ($response->iterateAllElements() as $row) {
            return CampaignExternalData::fromGoogleResponse($row->getCampaign());
        }

        return null;
    }
}
