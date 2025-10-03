<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\GetItemDetails;

/**
 * Data Transfer Object for eBay item details.
 *
 * Provides a stable, type-safe interface for working with detailed item information
 * returned from the eBay Browse API.
 */
final class GetItemDetailsDTO
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $title,
        public readonly array $price,
        public readonly ?string $condition = null,
        public readonly ?array $image = null,
        public readonly ?array $seller = null,
        public readonly ?array $shipping = null,
        public readonly ?array $returnTerms = null,
        public readonly ?array $product = null,
        public readonly ?string $description = null,
        public readonly ?array $additionalImages = null,
        public readonly ?string $itemWebUrl = null,
        public readonly ?array $localizedAspects = null,
    ) {}

    /**
     * Create from eBay API response.
     */
    public static function fromApiResponse(array $response): self
    {
        return new self(
            itemId: $response['itemId'] ?? '',
            title: $response['title'] ?? '',
            price: $response['price'] ?? [],
            condition: $response['condition'] ?? null,
            image: $response['image'] ?? null,
            seller: $response['seller'] ?? null,
            shipping: $response['shippingOptions'] ?? null,
            returnTerms: $response['returnTerms'] ?? null,
            product: $response['product'] ?? null,
            description: $response['description'] ?? null,
            additionalImages: $response['additionalImages'] ?? null,
            itemWebUrl: $response['itemWebUrl'] ?? null,
            localizedAspects: $response['localizedAspects'] ?? null,
        );
    }

    /**
     * Get formatted price string.
     */
    public function getFormattedPrice(): string
    {
        $value = $this->price['value'] ?? '0.00';
        $currency = $this->price['currency'] ?? 'USD';

        return "{$currency} {$value}";
    }

    /**
     * Check if item is new.
     */
    public function isNew(): bool
    {
        return strtoupper($this->condition ?? '') === 'NEW';
    }

    /**
     * Get seller username.
     */
    public function getSellerUsername(): ?string
    {
        return $this->seller['username'] ?? null;
    }

    /**
     * Get seller feedback percentage.
     */
    public function getSellerFeedbackPercentage(): ?float
    {
        return isset($this->seller['feedbackPercentage'])
            ? (float) $this->seller['feedbackPercentage']
            : null;
    }

    /**
     * Check if shipping is free.
     */
    public function hasFreeShipping(): bool
    {
        if (empty($this->shipping)) {
            return false;
        }

        foreach ($this->shipping as $option) {
            if (isset($option['shippingCost']['value'])
                && (float) $option['shippingCost']['value'] === 0.0
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get cheapest shipping cost.
     */
    public function getCheapestShippingCost(): ?array
    {
        if (empty($this->shipping)) {
            return null;
        }

        $costs = array_filter(
            array_map(
                fn ($option) => $option['shippingCost'] ?? null,
                $this->shipping
            )
        );

        if (empty($costs)) {
            return null;
        }

        usort($costs, fn ($a, $b) => (float) ($a['value'] ?? PHP_FLOAT_MAX) <=> (float) ($b['value'] ?? PHP_FLOAT_MAX)
        );

        return $costs[0];
    }

    /**
     * Check if returns are accepted.
     */
    public function acceptsReturns(): bool
    {
        return ($this->returnTerms['returnsAccepted'] ?? false) === true;
    }

    /**
     * Get return period in days.
     */
    public function getReturnPeriodDays(): ?int
    {
        if (! isset($this->returnTerms['returnPeriod']['value'])) {
            return null;
        }

        $unit = $this->returnTerms['returnPeriod']['unit'] ?? 'DAY';
        $value = (int) $this->returnTerms['returnPeriod']['value'];

        // Convert to days if needed
        return match ($unit) {
            'DAY' => $value,
            'WEEK' => $value * 7,
            'MONTH' => $value * 30,
            default => $value,
        };
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'itemId' => $this->itemId,
            'title' => $this->title,
            'price' => $this->price,
            'condition' => $this->condition,
            'image' => $this->image,
            'seller' => $this->seller,
            'shipping' => $this->shipping,
            'returnTerms' => $this->returnTerms,
            'product' => $this->product,
            'description' => $this->description,
            'additionalImages' => $this->additionalImages,
            'itemWebUrl' => $this->itemWebUrl,
            'localizedAspects' => $this->localizedAspects,
        ];
    }
}
