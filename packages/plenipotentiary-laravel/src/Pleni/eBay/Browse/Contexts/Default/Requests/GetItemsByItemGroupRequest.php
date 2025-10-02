<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Retrieve all items that belong to a specific item group.
 * 
 * An item group is a set of items that are variations of the same product.
 * For example, a t-shirt that comes in different sizes and colors would be
 * in the same item group.
 * 
 * This request retrieves all the individual items (variations) within
 * a specified item group.
 * 
 * @see https://developer.ebay.com/api-docs/buy/browse/resources/item/methods/getItemsByItemGroup
 */
final class GetItemsByItemGroupRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param string $itemGroupId The unique identifier of the item group
     *                            This is returned in the item.itemGroupHref field
     */
    public function __construct(
        private readonly string $itemGroupId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/buy/browse/v1/item/get_items_by_item_group';
    }

    protected function defaultQuery(): array
    {
        return [
            'item_group_id' => $this->itemGroupId,
        ];
    }
}
