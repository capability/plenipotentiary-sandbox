<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Retrieve item details using a legacy eBay item ID.
 *
 * This request allows you to get item information using the older,
 * numeric eBay item IDs. It translates the legacy ID to the new
 * RESTful format and returns the item details.
 *
 * This is useful for backward compatibility when you have legacy
 * item IDs from older eBay APIs or systems.
 *
 * @see https://developer.ebay.com/api-docs/buy/browse/resources/item/methods/getItemByLegacyId
 */
final class GetItemByLegacyIdRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  string  $legacyItemId  The legacy numeric item ID (e.g., "110039490209")
     * @param  string|null  $fieldgroups  Control what is returned in response
     *                                    Options: PRODUCT, COMPACT, ADDITIONAL_SELLER_DETAILS, CHARITY_DETAILS
     * @param  string|null  $legacyVariationId  The legacy variation ID for multi-variation items
     * @param  string|null  $legacyVariationSku  The legacy variation SKU
     */
    public function __construct(
        private readonly string $legacyItemId,
        private readonly ?string $fieldgroups = null,
        private readonly ?string $legacyVariationId = null,
        private readonly ?string $legacyVariationSku = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/buy/browse/v1/item/get_item_by_legacy_id';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'legacy_item_id' => $this->legacyItemId,
            'fieldgroups' => $this->fieldgroups,
            'legacy_variation_id' => $this->legacyVariationId,
            'legacy_variation_sku' => $this->legacyVariationSku,
        ], fn ($value) => $value !== null);
    }
}
