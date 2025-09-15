<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Google\Ads\GoogleAds\V20\Services\SearchGoogleAdsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\AdGroupExternalData;

class AdGroupFinder
{
    public static function findByName(\Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient $client, string $customerId, string $name): ?AdGroupExternalData
    {
        $query = sprintf(
            "SELECT ad_group.id, ad_group.name, ad_group.status, ad_group.resource_name, ad_group.campaign, ad_group.cpc_bid_micros
             FROM ad_group
             WHERE ad_group.name = '%s'
             LIMIT 1",
            addslashes($name)
        );

        $request = new SearchGoogleAdsRequest;
        $request->setCustomerId($customerId);
        $request->setQuery($query);

        $response = $client->getGoogleAdsServiceClient()->search($request);

        foreach ($response->iterateAllElements() as $row) {
            return AdGroupExternalData::fromGoogleResponse($row->getAdGroup());
        }

        return null;
    }
}
