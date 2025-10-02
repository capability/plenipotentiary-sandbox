<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Retrieve the details of a specific eBay item.
 * 
 * This request retrieves comprehensive details of a specific item including:
 * - Description, price, category
 * - All item aspects and condition
 * - Return policies
 * - Seller feedback and score
 * - Shipping options, costs, and delivery estimates
 * - And other information buyers need to make purchasing decisions
 * 
 * @see https://developer.ebay.com/api-docs/buy/browse/resources/item/methods/getItem
 */
final class GetItemRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param string $itemId The unique RESTful identifier of the item
     *                       Format: v1|<legacyItemId>|<legacyVariationId>
     *                       Example: v1|272535166916|0
     * @param string|null $fieldgroups Control what is returned in response
     *                                  Options: PRODUCT, COMPACT, ADDITIONAL_SELLER_DETAILS, CHARITY_DETAILS
     *                                  Multiple can be used (comma-separated), but COMPACT must be alone
     * @param int|null $quantityForShippingEstimate Item quantity for shipping calculation
     */
    public function __construct(
        private readonly string $itemId,
        private readonly ?string $fieldgroups = null,
        private readonly ?int $quantityForShippingEstimate = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/buy/browse/v1/item/{$this->itemId}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'fieldgroups' => $this->fieldgroups,
            'quantity_for_shipping_estimate' => $this->quantityForShippingEstimate 
                ? (string) $this->quantityForShippingEstimate 
                : null,
        ], fn($value) => $value !== null);
    }
}
