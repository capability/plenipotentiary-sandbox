<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support;

use Google\Ads\GoogleAds\V20\Services\SearchGoogleAdsRequest;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\AdExternalData;

class AdFinder
{
    public static function findById(\Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient $client, string $customerId, int $id): ?AdExternalData
    {
        $query = sprintf(
            'SELECT ad.id, ad.resource_name, ad.final_urls, ad.responsive_search_ad.headlines, ad.responsive_search_ad.descriptions, ad.responsive_search_ad.path1, ad.responsive_search_ad.path2
             FROM ad
             WHERE ad.id = %d
             LIMIT 1',
            $id
        );

        $request = new SearchGoogleAdsRequest;
        $request->setCustomerId($customerId);
        $request->setQuery($query);

        $response = $client->getGoogleAdsServiceClient()->search($request);

        foreach ($response->iterateAllElements() as $row) {
            return AdExternalData::fromGoogleResponse($row->getAd());
        }

        return null;
    }
}
