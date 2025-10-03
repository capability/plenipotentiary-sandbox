<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\GetItemAction;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\SearchByImageAction;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\SearchItemsAction;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\GetItemDetails\GetItemDetailsOperation;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\SearchItems\SearchItemsOperation;

/**
 * Controller for eBay item search functionality using the Operation pattern.
 *
 * This demonstrates how developers would expose eBay Browse API
 * functionality through their Laravel application's web routes.
 *
 * Example routes:
 *   Route::get('/api/ebay/search', [ItemSearchController::class, 'search']);
 *   Route::get('/api/ebay/items/{itemId}', [ItemSearchController::class, 'show']);
 *   Route::post('/api/ebay/search-by-image', [ItemSearchController::class, 'searchByImage']);
 */
class ItemSearchController extends Controller
{
    public function __construct(
        private readonly SearchItemsAction $searchAction,
        private readonly GetItemAction $getItemAction,
        private readonly SearchByImageAction $searchByImageAction,
    ) {}

    /**
     * Search for items by keyword.
     *
     * GET /api/ebay/search?q=laptop&category=58058&limit=20
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|max:255',
            'category' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:200',
            'offset' => 'nullable|integer|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort' => 'nullable|string|in:price,newlyListed,endingSoonest,distance',
        ]);

        // Build operation data
        $operationData = [
            'query' => $validated['q'],
            'limit' => $validated['limit'] ?? 50,
            'offset' => $validated['offset'] ?? 0,
        ];

        // Add category filter
        if (isset($validated['category'])) {
            $operationData['categoryIds'] = $validated['category'];
        }

        // Build price filter
        if (isset($validated['min_price']) || isset($validated['max_price'])) {
            $min = $validated['min_price'] ?? '0';
            $max = $validated['max_price'] ?? '*';
            $operationData['filter'] = "price:[{$min}..{$max}]";
        }

        // Add sort
        if (isset($validated['sort'])) {
            $operationData['sort'] = $validated['sort'];
        }

        try {
            // Create and validate the operation
            $operation = SearchItemsOperation::fromArray($operationData);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_PARAMETERS',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }

        // Execute search
        $result = $this->searchAction->execute($operation);

        if ($result->isErr()) {
            $error = $result->unwrapErr();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $error['code'] ?? 'UNKNOWN_ERROR',
                    'message' => $error['message'] ?? 'An error occurred',
                ],
            ], $error['httpStatus'] ?? 500);
        }

        // Return the DTO's structured data
        $dto = $result->unwrap();

        return response()->json([
            'success' => true,
            'data' => $dto->toArray(),
        ]);
    }

    /**
     * Get item details by ID.
     *
     * GET /api/ebay/items/{itemId}
     */
    public function show(string $itemId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'compact' => 'nullable|boolean',
            'with_product' => 'nullable|boolean',
        ]);

        try {
            // Create the appropriate operation
            if ($validated['compact'] ?? false) {
                $operation = GetItemDetailsOperation::compact($itemId);
            } elseif ($validated['with_product'] ?? false) {
                $operation = GetItemDetailsOperation::withProduct($itemId);
            } else {
                $operation = new GetItemDetailsOperation($itemId);
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_ITEM_ID',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }

        $result = $this->getItemAction->execute($operation);

        if ($result->isErr()) {
            $error = $result->unwrapErr();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $error['code'] ?? 'UNKNOWN_ERROR',
                    'message' => $error['message'] ?? 'An error occurred',
                ],
            ], $error['httpStatus'] ?? 500);
        }

        // Return the DTO's structured data
        $dto = $result->unwrap();

        return response()->json([
            'success' => true,
            'data' => $dto->toArray(),
        ]);
    }

    /**
     * Search for items using an uploaded image.
     *
     * POST /api/ebay/search-by-image
     * Content-Type: multipart/form-data
     * Body: image (file)
     */
    public function searchByImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
            'category' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        // Read and encode the uploaded image
        $image = $request->file('image');
        $imageData = file_get_contents($image->getRealPath());
        $base64Image = base64_encode($imageData);

        $options = [
            'limit' => $validated['limit'] ?? 50,
        ];

        if (isset($validated['category'])) {
            $options['categoryIds'] = $validated['category'];
        }

        // Note: SearchByImageAction could be refactored to use Operation pattern
        // For now, keeping it as-is since it's a simpler use case
        $result = $this->searchByImageAction->execute($base64Image, $options);

        if ($result->isErr()) {
            $error = $result->unwrapErr();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $error['code'] ?? 'UNKNOWN_ERROR',
                    'message' => $error['message'] ?? 'An error occurred',
                ],
            ], $error['httpStatus'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'data' => $result->unwrap(),
        ]);
    }

    /**
     * Get price statistics for a search query.
     *
     * GET /api/ebay/price-stats?q=laptop
     */
    public function priceStats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|max:255',
            'category' => 'nullable|string',
        ]);

        try {
            // Create operation for price analysis
            $operation = new SearchItemsOperation(
                query: $validated['q'],
                limit: 200, // Get more items for better statistics
                categoryIds: $validated['category'] ?? null,
                sort: 'price', // Sort by price to get range
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_PARAMETERS',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }

        $result = $this->searchAction->execute($operation);

        if ($result->isErr()) {
            $error = $result->unwrapErr();

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $error['code'] ?? 'UNKNOWN_ERROR',
                    'message' => $error['message'] ?? 'An error occurred',
                ],
            ], $error['httpStatus'] ?? 500);
        }

        $dto = $result->unwrap();

        // Use the DTO's built-in price range calculation
        $priceRange = $dto->getPriceRange();

        if (! $priceRange) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_PRICE_DATA',
                    'message' => 'No price data available for this search',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'count' => count($dto->items),
                'priceRange' => $priceRange,
                'pagination' => [
                    'total' => $dto->total,
                    'analyzed' => count($dto->items),
                ],
            ],
        ]);
    }
}
