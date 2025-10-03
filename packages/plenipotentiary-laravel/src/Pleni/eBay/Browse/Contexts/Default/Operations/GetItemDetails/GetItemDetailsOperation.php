<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\GetItemDetails;

/**
 * Operation for retrieving detailed information about a specific eBay item.
 *
 * This operation encapsulates the request to get full details for a single item,
 * including shipping, return policy, seller information, and product details.
 */
final class GetItemDetailsOperation
{
    /**
     * @param  string  $itemId  The eBay item ID
     * @param  string|null  $fieldgroups  Optional field groups (PRODUCT, COMPACT, ADDITIONAL_SELLER_DETAILS)
     */
    public function __construct(
        public readonly string $itemId,
        public readonly ?string $fieldgroups = null,
    ) {
        $this->validate();
    }

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            itemId: $data['itemId'] ?? $data['item_id'] ?? '',
            fieldgroups: $data['fieldgroups'] ?? null,
        );
    }

    /**
     * Create with COMPACT field group (minimal data).
     */
    public static function compact(string $itemId): self
    {
        return new self($itemId, 'COMPACT');
    }

    /**
     * Create with PRODUCT field group (includes product details).
     */
    public static function withProduct(string $itemId): self
    {
        return new self($itemId, 'PRODUCT');
    }

    /**
     * Create with additional seller details.
     */
    public static function withSellerDetails(string $itemId): self
    {
        return new self($itemId, 'ADDITIONAL_SELLER_DETAILS');
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'itemId' => $this->itemId,
            'fieldgroups' => $this->fieldgroups,
        ];
    }

    private function validate(): void
    {
        if (empty(trim($this->itemId))) {
            throw new \InvalidArgumentException('Item ID is required');
        }

        if ($this->fieldgroups !== null) {
            $validFieldgroups = ['PRODUCT', 'COMPACT', 'ADDITIONAL_SELLER_DETAILS'];
            if (! in_array($this->fieldgroups, $validFieldgroups, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Invalid fieldgroups. Must be one of: %s',
                        implode(', ', $validFieldgroups)
                    )
                );
            }
        }
    }
}
