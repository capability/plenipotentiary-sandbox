<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\SearchItems;

/**
 * Data Transfer Object for eBay item search results.
 * 
 * This DTO represents the structured output of a search operation.
 * It provides a stable, documented interface for consuming search results
 * regardless of how eBay's API response format might evolve.
 * 
 * Benefits:
 * - Type-safe access to search results
 * - Decouples domain from eBay's response structure
 * - Can be persisted, cached, or queued
 * - Clear documentation of what data is available
 */
final class SearchItemsDTO
{
    /**
     * @param array $items Array of item summaries
     * @param int $total Total number of matching items
     * @param int $limit Number of items per page
     * @param int $offset Current offset
     * @param array|null $refinements Available refinement options (categories, aspects)
     * @param array|null $warnings Any warnings from the API
     */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
        public readonly int $limit,
        public readonly int $offset,
        public readonly ?array $refinements = null,
        public readonly ?array $warnings = null,
    ) {}

    /**
     * Create from eBay API response.
     * 
     * This factory method knows how to parse eBay's specific response format
     * and extract the relevant data into our stable DTO structure.
     */
    public static function fromApiResponse(array $response): self
    {
        return new self(
            items: $response['itemSummaries'] ?? [],
            total: $response['total'] ?? 0,
            limit: $response['limit'] ?? 50,
            offset: $response['offset'] ?? 0,
            refinements: isset($response['refinement']) ? [
                'aspectRefinements' => $response['refinement']['aspectDistributions'] ?? [],
                'categoryRefinements' => $response['refinement']['categoryDistributions'] ?? [],
                'conditionRefinements' => $response['refinement']['conditionDistributions'] ?? [],
            ] : null,
            warnings: $response['warnings'] ?? null,
        );
    }

    /**
     * Get total number of pages.
     */
    public function getTotalPages(): int
    {
        if ($this->limit === 0) {
            return 0;
        }

        return (int) ceil($this->total / $this->limit);
    }

    /**
     * Get current page number (1-indexed).
     */
    public function getCurrentPage(): int
    {
        if ($this->limit === 0) {
            return 0;
        }

        return (int) floor($this->offset / $this->limit) + 1;
    }

    /**
     * Check if there are more results available.
     */
    public function hasMoreResults(): bool
    {
        return ($this->offset + count($this->items)) < $this->total;
    }

    /**
     * Get the next offset for pagination.
     */
    public function getNextOffset(): ?int
    {
        if (!$this->hasMoreResults()) {
            return null;
        }

        return $this->offset + $this->limit;
    }

    /**
     * Extract just the item IDs.
     */
    public function getItemIds(): array
    {
        return array_map(
            fn($item) => $item['itemId'] ?? null,
            $this->items
        );
    }

    /**
     * Extract items with specific condition.
     */
    public function filterByCondition(string $condition): array
    {
        return array_filter(
            $this->items,
            fn($item) => ($item['condition'] ?? null) === $condition
        );
    }

    /**
     * Get price range from results.
     */
    public function getPriceRange(): ?array
    {
        if (empty($this->items)) {
            return null;
        }

        $prices = array_map(
            fn($item) => (float) ($item['price']['value'] ?? 0),
            $this->items
        );

        $prices = array_filter($prices);

        if (empty($prices)) {
            return null;
        }

        return [
            'min' => min($prices),
            'max' => max($prices),
            'average' => array_sum($prices) / count($prices),
            'currency' => $this->items[0]['price']['currency'] ?? 'USD',
        ];
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'refinements' => $this->refinements,
            'warnings' => $this->warnings,
            'pagination' => [
                'currentPage' => $this->getCurrentPage(),
                'totalPages' => $this->getTotalPages(),
                'hasMore' => $this->hasMoreResults(),
                'nextOffset' => $this->getNextOffset(),
            ],
        ];
    }
}
