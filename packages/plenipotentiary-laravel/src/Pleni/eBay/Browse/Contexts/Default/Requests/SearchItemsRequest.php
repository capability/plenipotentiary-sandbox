<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Search for eBay items by various query parameters.
 *
 * This request searches for eBay items and retrieves summaries. You can search by:
 * - Keyword (q parameter)
 * - Category ID
 * - eBay product ID (ePID)
 * - GTIN (UPC, EAN, ISBN)
 * - Or a combination of these
 *
 * The request supports filtering, pagination, sorting, and aspect refinements.
 *
 * @see https://developer.ebay.com/api-docs/buy/browse/resources/item_summary/methods/search
 */
final class SearchItemsRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  string|null  $query  Search keyword (e.g., "laptop", "phone")
     * @param  int  $limit  Number of items per page (1-200, default: 50)
     * @param  int  $offset  Pagination offset (must be 0 or multiple of limit)
     * @param  string|null  $categoryIds  Comma-separated category IDs
     * @param  string|null  $filter  Filter string (e.g., "price:[10..50],sellers:{seller1|seller2}")
     * @param  string|null  $sort  Sort order (e.g., "price", "newlyListed", "distance")
     * @param  string|null  $aspectFilter  Aspect filter (e.g., "categoryId:15724,Color:{Red}")
     * @param  string|null  $fieldgroups  Field groups to include (e.g., "ASPECT_REFINEMENTS", "MATCHING_ITEMS")
     * @param  string|null  $gtin  Global Trade Item Number (UPC, EAN, ISBN)
     * @param  string|null  $epid  eBay Product ID
     * @param  string|null  $autoCorrect  Auto-correct keywords ("KEYWORD")
     * @param  string|null  $compatibilityFilter  Compatibility filter for automotive/parts
     */
    public function __construct(
        private readonly ?string $query = null,
        private readonly int $limit = 50,
        private readonly int $offset = 0,
        private readonly ?string $categoryIds = null,
        private readonly ?string $filter = null,
        private readonly ?string $sort = null,
        private readonly ?string $aspectFilter = null,
        private readonly ?string $fieldgroups = null,
        private readonly ?string $gtin = null,
        private readonly ?string $epid = null,
        private readonly ?string $autoCorrect = null,
        private readonly ?string $compatibilityFilter = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/buy/browse/v1/item_summary/search';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'q' => $this->query,
            'limit' => (string) $this->limit,
            'offset' => (string) $this->offset,
            'category_ids' => $this->categoryIds,
            'filter' => $this->filter,
            'sort' => $this->sort,
            'aspect_filter' => $this->aspectFilter,
            'fieldgroups' => $this->fieldgroups,
            'gtin' => $this->gtin,
            'epid' => $this->epid,
            'auto_correct' => $this->autoCorrect,
            'compatibility_filter' => $this->compatibilityFilter,
        ], fn ($value) => $value !== null);
    }
}
