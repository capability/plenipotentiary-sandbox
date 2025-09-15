<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Ebay\Browse\ItemSummary\Service\Generated;

use Illuminate\Support\Facades\Http;
use Plenipotentiary\Laravel\Contracts\AuthStrategy;
use Plenipotentiary\Laravel\Contracts\SearchResult;
use Plenipotentiary\Laravel\Pleni\Ebay\Browse\ItemSummary\DTO\External\BrowseItemSummary;
use Plenipotentiary\Laravel\Pleni\Ebay\Browse\ItemSummary\Translate\EbayBrowseToDomainMapper;

/**
 * Generated service for calling the eBay Browse "item_summary/search" endpoint.
 * This class may be regenerated; do not put custom logic here.
 */
class EbayBrowseItemSummaryService
{
    public function __construct(
        protected AuthStrategy $auth
    ) {}

    /**
     * Execute a keyword search against eBay Browse.
     */
    public function search(string $keywords): SearchResult
    {
        $url = 'https://api.ebay.com/buy/browse/v1/item_summary/search';
        $headers = $this->auth->getHeaders(['https://api.ebay.com/oauth/api_scope/buy.browse']);

        $response = Http::withHeaders($headers)
            ->get($url, ['q' => $keywords])
            ->throw()
            ->json();

        $items = [];
        foreach ($response['itemSummaries'] ?? [] as $row) {
            $external = BrowseItemSummary::fromArray($row);
            $items[] = EbayBrowseToDomainMapper::toDomain($external);
        }

        return new \Plenipotentiary\Laravel\Support\SearchResultCollection($items, $response['total'] ?? null);
    }
}
