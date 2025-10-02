<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions;

use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\SearchItems\SearchItemsDTO;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\SearchItems\SearchItemsGateway;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\SearchItems\SearchItemsOperation;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Action for searching eBay items using the Operation pattern.
 * 
 * This action demonstrates how developers interact with the eBay Browse API
 * in their application layer. The action is:
 * - Domain-focused (knows about searching items, not eBay specifics)
 * - Type-safe (uses Operation and DTO objects)
 * - Testable (can mock the gateway)
 * - Reusable across controllers, jobs, commands
 * 
 * The Operation pattern provides:
 * - Structured, validated input via SearchItemsOperation
 * - Stable gateway interface via SearchItemsGateway
 * - Type-safe output via SearchItemsDTO
 */
final class SearchItemsAction
{
    public function __construct(
        private readonly SearchItemsGateway $gateway,
    ) {}

    /**
     * Search for eBay items using a pre-built operation.
     * 
     * This is the primary method - it accepts a fully-formed operation object
     * that has been validated and is ready to execute.
     * 
     * @param SearchItemsOperation $operation The search operation to execute
     * @return Result<SearchItemsDTO, array> Success contains search results DTO
     */
    public function execute(SearchItemsOperation $operation): Result
    {
        $result = $this->gateway->execute($operation);

        // Transform successful results into a DTO
        if ($result->isOk()) {
            $data = $result->unwrap();
            $dto = SearchItemsDTO::fromApiResponse($data);
            return Result::ok($dto);
        }

        return $result;
    }

    /**
     * Search for eBay items by keyword (convenience method).
     * 
     * @param string $query Search keyword (e.g., "laptop", "vintage camera")
     * @param array $options Additional search options (see SearchItemsOperation)
     * @return Result<SearchItemsDTO, array>
     */
    public function search(string $query, array $options = []): Result
    {
        $operation = SearchItemsOperation::fromArray([
            'query' => $query,
            ...$options,
        ]);

        return $this->execute($operation);
    }

    /**
     * Search for items with price filter.
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
     * Search for items in a specific category.
     */
    public function searchInCategory(
        string $query,
        string $categoryId,
        array $options = []
    ): Result {
        $operation = SearchItemsOperation::fromArray([
            'query' => $query,
            'categoryIds' => $categoryId,
            ...$options,
        ]);

        return $this->execute($operation);
    }
}
