<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Search for eBay items using an image.
 *
 * This request allows you to search for items on eBay using an image.
 * The image can be provided as a base64-encoded string.
 *
 * This is useful for visual search functionality where users can
 * upload or provide an image to find similar items.
 *
 * @see https://developer.ebay.com/api-docs/buy/browse/resources/item_summary/methods/searchByImage
 */
final class SearchByImageRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  string  $image  Base64-encoded image string
     * @param  string|null  $categoryIds  Comma-separated category IDs to limit search
     * @param  string|null  $filter  Filter string (e.g., "price:[10..50]")
     * @param  int  $limit  Number of items per page (1-200, default: 50)
     * @param  int  $offset  Pagination offset
     * @param  string|null  $sort  Sort order
     * @param  string|null  $aspectFilter  Aspect filter
     * @param  string|null  $fieldgroups  Field groups to include
     */
    public function __construct(
        private readonly string $image,
        private readonly ?string $categoryIds = null,
        private readonly ?string $filter = null,
        private readonly int $limit = 50,
        private readonly int $offset = 0,
        private readonly ?string $sort = null,
        private readonly ?string $aspectFilter = null,
        private readonly ?string $fieldgroups = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/buy/browse/v1/item_summary/search_by_image';
    }

    protected function defaultBody(): array
    {
        return [
            'image' => $this->image,
        ];
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'category_ids' => $this->categoryIds,
            'filter' => $this->filter,
            'limit' => (string) $this->limit,
            'offset' => (string) $this->offset,
            'sort' => $this->sort,
            'aspect_filter' => $this->aspectFilter,
            'fieldgroups' => $this->fieldgroups,
        ], fn ($value) => $value !== null);
    }
}
