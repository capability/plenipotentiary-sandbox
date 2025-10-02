<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\SearchItems;

/**
 * Operation representing a search for eBay items.
 * 
 * This operation encapsulates all the parameters needed to perform
 * an eBay item search. It serves as a stable, serializable contract
 * between the domain layer and the gateway/adapter.
 * 
 * Benefits:
 * - Type-safe parameter passing
 * - Queueable (all properties are serializable)
 * - Validation can be applied at construction
 * - Clear documentation of what a "search" requires
 * - Testable in isolation
 */
final class SearchItemsOperation
{
    /**
     * @param string $query Search keyword or phrase
     * @param int $limit Number of results to return (1-200)
     * @param int $offset Pagination offset (must be 0 or multiple of limit)
     * @param string|null $categoryIds Comma-separated category IDs to filter by
     * @param string|null $filter Advanced filter string (e.g., "price:[10..50],condition:{New}")
     * @param string|null $sort Sort order (price, newlyListed, endingSoonest, distance)
     * @param string|null $aspectFilter Aspect filter for refinement (e.g., "categoryId:15724,Color:{Red}")
     * @param string|null $fieldgroups Field groups to include (ASPECT_REFINEMENTS, MATCHING_ITEMS, FULL)
     * @param string|null $gtin Global Trade Item Number (UPC, EAN, ISBN)
     * @param string|null $epid eBay Product ID
     * @param bool $autoCorrect Whether to auto-correct the query keywords
     * @param string|null $compatibilityFilter Compatibility filter for automotive parts
     */
    public function __construct(
        public readonly string $query,
        public readonly int $limit = 50,
        public readonly int $offset = 0,
        public readonly ?string $categoryIds = null,
        public readonly ?string $filter = null,
        public readonly ?string $sort = null,
        public readonly ?string $aspectFilter = null,
        public readonly ?string $fieldgroups = null,
        public readonly ?string $gtin = null,
        public readonly ?string $epid = null,
        public readonly bool $autoCorrect = false,
        public readonly ?string $compatibilityFilter = null,
    ) {
        $this->validate();
    }

    /**
     * Create from an array of parameters.
     * 
     * Useful for building operations from request data, config files, etc.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            query: $data['query'] ?? $data['q'] ?? '',
            limit: $data['limit'] ?? 50,
            offset: $data['offset'] ?? 0,
            categoryIds: $data['categoryIds'] ?? $data['category_ids'] ?? null,
            filter: $data['filter'] ?? null,
            sort: $data['sort'] ?? null,
            aspectFilter: $data['aspectFilter'] ?? $data['aspect_filter'] ?? null,
            fieldgroups: $data['fieldgroups'] ?? null,
            gtin: $data['gtin'] ?? null,
            epid: $data['epid'] ?? null,
            autoCorrect: $data['autoCorrect'] ?? $data['auto_correct'] ?? false,
            compatibilityFilter: $data['compatibilityFilter'] ?? $data['compatibility_filter'] ?? null,
        );
    }

    /**
     * Convert to array for serialization/logging.
     */
    public function toArray(): array
    {
        return [
            'query' => $this->query,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'categoryIds' => $this->categoryIds,
            'filter' => $this->filter,
            'sort' => $this->sort,
            'aspectFilter' => $this->aspectFilter,
            'fieldgroups' => $this->fieldgroups,
            'gtin' => $this->gtin,
            'epid' => $this->epid,
            'autoCorrect' => $this->autoCorrect,
            'compatibilityFilter' => $this->compatibilityFilter,
        ];
    }

    /**
     * Validate the operation parameters.
     * 
     * @throws \InvalidArgumentException
     */
    private function validate(): void
    {
        if (empty(trim($this->query)) && empty($this->gtin) && empty($this->epid)) {
            throw new \InvalidArgumentException(
                'Search requires at least one of: query, gtin, or epid'
            );
        }

        if ($this->limit < 1 || $this->limit > 200) {
            throw new \InvalidArgumentException(
                'Limit must be between 1 and 200'
            );
        }

        if ($this->offset < 0) {
            throw new \InvalidArgumentException(
                'Offset must be non-negative'
            );
        }

        if ($this->limit > 0 && $this->offset % $this->limit !== 0) {
            throw new \InvalidArgumentException(
                'Offset must be 0 or a multiple of limit'
            );
        }
    }
}
