# eBay Browse API - Quick Start Guide

Get started with the eBay Browse API integration in under 5 minutes.

## Step 1: Install Dependencies

```bash
composer require saloonphp/saloon
```

## Step 2: Configure Credentials

Add to your `.env`:

```env
EBAY_ACCESS_TOKEN=your_production_access_token
EBAY_MARKETPLACE_ID=EBAY_US
EBAY_SANDBOX=false
```

## Step 3: Register Service Provider

Add to `config/app.php`:

```php
'providers' => [
    // ... other providers
    Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Providers\eBayBrowseServiceProvider::class,
],
```

Or if using Laravel 11+ auto-discovery, it should be picked up automatically.

## Step 4: Use It!

### Example 1: Search in a Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\SearchItemsAction;

class EbaySearchController extends Controller
{
    public function search(Request $request, SearchItemsAction $searchAction)
    {
        $result = $searchAction->execute(
            query: $request->input('q', 'laptop'),
            options: [
                'limit' => $request->input('limit', 50),
                'filter' => 'price:[100..1000]',
            ]
        );

        if ($result->isErr()) {
            return response()->json([
                'error' => $result->unwrapErr()
            ], 500);
        }

        return response()->json([
            'data' => $result->unwrap()
        ]);
    }
}
```

### Example 2: CLI Command

```bash
php artisan ebay:search "vintage camera" --limit=20
php artisan ebay:get-item "v1|272535166916|0"
```

### Example 3: Background Job

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\GetItemAction;

class FetchEbayItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private string $itemId,
        private int $productId
    ) {}

    public function handle(GetItemAction $getItemAction): void
    {
        $result = $getItemAction->execute($this->itemId);

        if ($result->isOk()) {
            $itemData = $result->unwrap();
            
            // Save to your database
            Product::where('id', $this->productId)->update([
                'ebay_data' => $itemData,
                'last_synced_at' => now(),
            ]);
        }
    }
}

// Dispatch it
FetchEbayItemJob::dispatch('v1|272535166916|0', 123)
    ->onQueue('ebay')
    ->delay(now()->addMinutes(5));
```

### Example 4: Direct Request (Advanced)

```php
use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests\SearchItemsRequest;

class AdvancedSearchService
{
    public function __construct(
        private RestGatewayContract $gateway
    ) {}

    public function searchWithComplexFilters(): array
    {
        $request = new SearchItemsRequest(
            query: 'laptop',
            limit: 100,
            offset: 0,
            categoryIds: '58058', // Computers/Tablets & Networking
            filter: 'price:[500..2000],conditions:{NEW|REFURBISHED}',
            sort: 'price',
            aspectFilter: 'categoryId:58058,Brand:{Apple|Dell|HP}',
            fieldgroups: 'ASPECT_REFINEMENTS'
        );

        $result = $this->gateway->execute($request);

        return $result->isOk() 
            ? $result->unwrap() 
            : [];
    }
}
```

## Available Actions

| Action | Purpose | Method |
|--------|---------|--------|
| `SearchItemsAction` | Search for items | `execute(string $query, array $options)` |
| `GetItemAction` | Get item details | `execute(string $itemId, array $options)` |
| `SearchByImageAction` | Visual search | `execute(string $base64Image, array $options)` |

## Available Requests (Direct Gateway Use)

| Request | Method | Endpoint |
|---------|--------|----------|
| `SearchItemsRequest` | GET | /item_summary/search |
| `GetItemRequest` | GET | /item/{itemId} |
| `SearchByImageRequest` | POST | /item_summary/search_by_image |
| `GetItemByLegacyIdRequest` | GET | /item/get_item_by_legacy_id |
| `GetItemsByItemGroupRequest` | GET | /item/get_items_by_item_group |
| `CheckCompatibilityRequest` | POST | /item/{itemId}/check_compatibility |

## Common Use Cases

### Search with Price Range

```php
$result = $searchAction->execute('gaming laptop', [
    'filter' => 'price:[500..1500]',
    'limit' => 50,
]);
```

### Search in Specific Category

```php
$result = $searchAction->searchInCategory(
    query: 'iPhone',
    categoryId: '9355', // Cell Phones & Smartphones
    options: ['limit' => 100]
);
```

### Get Item with Product Info

```php
$result = $getItemAction->getWithProduct('v1|272535166916|0');
```

### Check for Item Changes (Compact Mode)

```php
$result = $getItemAction->getCompact('v1|272535166916|0');
$data = $result->unwrap();

// Check if item was revised
if (isset($data['sellerItemRevision'])) {
    // Item has changed since last check
}
```

### Visual Search from File

```php
$result = $searchByImageAction->executeFromFile(
    filePath: storage_path('uploads/search-image.jpg'),
    options: ['categoryIds' => '550', 'limit' => 30]
);
```

## Error Handling

All methods return a `Result` object:

```php
$result = $searchAction->execute('laptop');

if ($result->isOk()) {
    $data = $result->unwrap();
    // Process successful response
} else {
    $error = $result->unwrapErr();
    // $error is an array with:
    // - code: Error code
    // - message: Human-readable message
    // - httpStatus: HTTP status code
    // - retryable: Whether the error is retryable
}
```

## Testing

### Mock the Action

```php
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\SearchItemsAction;
use Plenipotentiary\Laravel\Support\Result;

test('can search ebay items', function () {
    $searchAction = Mockery::mock(SearchItemsAction::class);
    $searchAction->shouldReceive('execute')
        ->with('laptop', Mockery::any())
        ->andReturn(Result::ok([
            'itemSummaries' => [
                ['title' => 'Gaming Laptop', 'price' => ['value' => 999]],
            ],
            'total' => 1,
        ]));

    $this->app->instance(SearchItemsAction::class, $searchAction);

    $response = $this->getJson('/api/ebay/search?q=laptop');
    $response->assertOk();
});
```

### Integration Test

```php
test('ebay search returns results', function () {
    // Make sure you have valid credentials in .env.testing
    $response = $this->postJson('/api/ebay/search', [
        'q' => 'laptop',
        'limit' => 10,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'itemSummaries',
                'total',
            ],
        ]);
});
```

## Logging

All requests are automatically logged:

```
[2024-01-15 10:23:45] local.INFO: eBay Browse REST Gateway: Executing request {"request_class":"SearchItemsRequest"}
[2024-01-15 10:23:46] local.INFO: eBay Browse REST: Executing request {"request_class":"SearchItemsRequest"}
```

## Rate Limiting

The error mapper automatically detects rate limit errors and marks them as retryable. The gateway will apply exponential backoff for retries.

## Sandbox Mode

For testing, use sandbox:

```env
EBAY_SANDBOX=true
EBAY_ACCESS_TOKEN=your_sandbox_token
```

The connector will automatically use `https://api.sandbox.ebay.com` instead of production.

## Next Steps

- Read the [full README](README.md) for detailed documentation
- Check [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) for architecture details
- Refer to [eBay Browse API docs](https://developer.ebay.com/api-docs/buy/browse/overview.html) for parameter details

## Support

For issues or questions:
1. Check the [README](README.md)
2. Review [PATTERN_DECISION_GUIDE.md](../../docs/PATTERN_DECISION_GUIDE.md)
3. Read the package [AGENTS.md](../../AGENTS.md)

---

**That's it!** You now have a fully functional eBay Browse API integration.
