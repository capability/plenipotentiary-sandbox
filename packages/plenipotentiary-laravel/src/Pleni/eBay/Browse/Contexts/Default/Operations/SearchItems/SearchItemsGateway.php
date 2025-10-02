<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\SearchItems;

use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests\SearchItemsRequest;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Gateway for the SearchItems operation.
 * 
 * This gateway acts as the stable interface for executing eBay item searches.
 * It handles:
 * - Operation validation
 * - Translation from Operation to Request
 * - Delegation to the REST gateway for execution
 * - Cross-cutting concerns (logging, monitoring, idempotency)
 * 
 * The gateway is the boundary between the domain layer (which knows about
 * "searching for items") and the transfer layer (which knows how to talk
 * to eBay's REST API).
 */
final class SearchItemsGateway
{
    public function __construct(
        private readonly RestGatewayContract $restGateway,
    ) {}

    /**
     * Execute a search operation.
     * 
     * This method accepts the domain-focused SearchItemsOperation object
     * and translates it into an eBay-specific REST request.
     * 
     * @param SearchItemsOperation $operation The search operation to execute
     * @return Result<array, array> Success contains search results, error contains failure info
     */
    public function execute(SearchItemsOperation $operation): Result
    {
        // Translate the domain operation into an eBay-specific request
        $request = new SearchItemsRequest(
            query: $operation->query,
            limit: $operation->limit,
            offset: $operation->offset,
            categoryIds: $operation->categoryIds,
            filter: $operation->filter,
            sort: $operation->sort,
            aspectFilter: $operation->aspectFilter,
            fieldgroups: $operation->fieldgroups,
            gtin: $operation->gtin,
            epid: $operation->epid,
            autoCorrect: $operation->autoCorrect ? 'KEYWORD' : null,
            compatibilityFilter: $operation->compatibilityFilter,
        );

        // Execute through the REST gateway
        // The REST gateway handles:
        // - HTTP execution via Saloon
        // - Response parsing
        // - Error mapping
        // - Logging and monitoring
        return $this->restGateway->execute($request);
    }

    /**
     * Execute multiple searches in parallel (if supported by infrastructure).
     * 
     * This demonstrates how the gateway can provide higher-level capabilities
     * while still maintaining the clean separation of concerns.
     */
    public function executeMany(array $operations): array
    {
        $results = [];

        foreach ($operations as $key => $operation) {
            if (!$operation instanceof SearchItemsOperation) {
                throw new \InvalidArgumentException(
                    'All operations must be instances of SearchItemsOperation'
                );
            }

            $results[$key] = $this->execute($operation);
        }

        return $results;
    }

    /**
     * Convenience method: Search by keyword with common defaults.
     */
    public function searchByKeyword(string $query, int $limit = 50): Result
    {
        $operation = new SearchItemsOperation(
            query: $query,
            limit: $limit,
        );

        return $this->execute($operation);
    }

    /**
     * Convenience method: Search with price range filter.
     */
    public function searchWithPriceRange(
        string $query,
        float $minPrice,
        float $maxPrice,
        int $limit = 50
    ): Result {
        $filter = "price:[{$minPrice}..{$maxPrice}]";
        
        $operation = new SearchItemsOperation(
            query: $query,
            limit: $limit,
            filter: $filter,
        );

        return $this->execute($operation);
    }

    /**
     * Convenience method: Search in specific category.
     */
    public function searchInCategory(
        string $query,
        string $categoryId,
        int $limit = 50
    ): Result {
        $operation = new SearchItemsOperation(
            query: $query,
            limit: $limit,
            categoryIds: $categoryId,
        );

        return $this->execute($operation);
    }
}
