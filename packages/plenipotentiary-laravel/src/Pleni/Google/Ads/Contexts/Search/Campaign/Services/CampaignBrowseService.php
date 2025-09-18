<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Services;

use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Plenipotentiary\Laravel\Contracts\SearchServiceContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignExternalDTO;

/**
 * Read-only campaign search/browse service.
 */
class CampaignBrowseService implements SearchServiceContract
{
    public function __construct(
        protected GoogleAdsClient $client,
    ) {}

    public function search(mixed $criteria): iterable
    {
        // TODO integrate query logic against Google Ads API
        return [];
    }
}
