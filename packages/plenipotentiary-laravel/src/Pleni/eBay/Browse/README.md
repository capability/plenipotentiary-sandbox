# eBay Browse API Integration

This directory contains a complete implementation of the eBay Browse API using the **REST/Saloon Pattern** from the Plenipotentiary package.

## Overview

The eBay Browse API is a query/search-based API (not resource management), making it a perfect candidate for the **Operation Pattern** with dedicated Saloon Request classes. This implementation demonstrates how to integrate with a RESTful API while keeping your domain layer clean and provider-agnostic.

## Architecture

### The Pattern: REST/Saloon

```
Domain Layer (Your Code)
├── Actions/           ← Business logic, domain-focused
├── Commands/          ← Laravel CLI commands
├── Controllers/       ← HTTP controllers
└── Jobs/             ← Background jobs

Gateway Layer (Stable Interface)
└── Transfer/Rest/
    └── eBayBrowseRestGateway.php  ← Predictable, testable interface

Adapter Layer (eBay-Specific)
└── Transfer/Rest/
    ├── eBayBrowseRestAdapter.php    ← Handles communication
    └── eBayBrowseRestConnector.php  ← Saloon connector

Request Layer (API Endpoints)
└── Contexts/Default/Requests/
    ├── SearchItemsRequest.php
    ├── GetItemRequest.php
    ├── SearchByImageRequest.php
    └── ... (one class per endpoint)
```

### Key Principle: Domain Ignorance

**All eBay-specific knowledge lives in the `Requests/` folder.** Your domain layer (Actions, Commands, Controllers) never imports eBay-specific code. They only depend on:
- The Gateway interface (`RestGatewayContract`)
- Saloon's `Request` class
- The `Result` object

This means:
- ✅ Your domain logic is portable
- ✅ Tests don't need eBay credentials
- ✅ Swapping providers doesn't break your domain
- ✅ Gateway provides logging, idempotency, retry logic

## Directory Structure

```
eBay/Browse/
├── Contexts/
│   └── Default/
│       ├── Actions/              ← Domain actions (your business logic)
│       │   ├── SearchItemsAction.php
│       │   ├── GetItemAction.php
│       │   └── SearchByImageAction.php
│       ├── Commands/             ← Laravel CLI commands
│       │   ├── SearchItemsCommand.php
│       │   └── GetItemCommand.php
│       ├── Controllers/          ← HTTP controllers
│       │   └── ItemSearchController.php
│       ├── Jobs/                 ← Background jobs
│       │   └── SyncItemDetailsJob.php
│       ├── Requests/             ← Saloon Request classes (eBay-specific)
│       │   ├── SearchItemsRequest.php
│       │   ├── GetItemRequest.php
│       │   ├── SearchByImageRequest.php
│       │   ├── GetItemByLegacyIdRequest.php
│       │   ├── GetItemsByItemGroupRequest.php
│       │   └── CheckCompatibilityRequest.php
│       └── Providers/            ← Service provider for IoC binding
│           └── eBayBrowseServiceProvider.php
└── Shared/
    ├── Auth/                     ← Authentication (if needed)
    ├── Support/                  ← Utilities
    │   ├── EbayConfig.php
    │   └── eBayErrorMapper.php
    └── Transfer/
        ├── Procedure/            ← Alternative: Simple RPC pattern
        └── Rest/                 ← REST/Saloon pattern (recommended)
            ├── eBayBrowseRestConnector.php
            ├── eBayBrowseRestAdapter.php
            └── eBayBrowseRestGateway.php
```

## Usage Examples

### 1. In a Controller

```php
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\SearchItemsAction;

class ProductController extends Controller
{
    public function search(Request $request, SearchItemsAction $searchAction)
    {
        $result = $searchAction->execute(
            query: $request->input('q'),
            options: [
                'limit' => 50,
                'categoryIds' => '58058', // Computers category
                'filter' => 'price:[100..500]',
            ]
        );

        if ($result->isErr()) {
            return response()->json(['error' => $result->unwrapErr()], 500);
        }

        return response()->json($result->unwrap());
    }
}
```

### 2. In a Command

```php
php artisan ebay:search "vintage camera" --limit=20
php artisan ebay:search "laptop" --category=58058 --min-price=300 --max-price=1000
php artisan ebay:get-item "v1|272535166916|0" --compact
```

### 3. In a Job (Queued)

```php
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Jobs\SyncItemDetailsJob;

// Dispatch to queue
SyncItemDetailsJob::dispatch('v1|272535166916|0', $productId)
    ->onQueue('ebay-sync')
    ->delay(now()->addMinutes(5));

// The job will:
// 1. Fetch item details from eBay
// 2. Handle retries automatically (via Laravel + Gateway)
// 3. Log all interactions
// 4. Persist to your local database
```

### 4. Direct API Usage (Advanced)

```php
use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests\SearchItemsRequest;

public function __construct(private RestGatewayContract $gateway) {}

public function search()
{
    // Create a type-safe request
    $request = new SearchItemsRequest(
        query: 'laptop',
        limit: 50,
        categoryIds: '58058',
        filter: 'price:[100..500]',
        sort: 'price'
    );

    // Execute through gateway
    $result = $this->gateway->execute($request);

    // Handle result
    if ($result->isOk()) {
        $data = $result->unwrap();
        $items = $data['itemSummaries'] ?? [];
        // ...
    }
}
```

## Available Endpoints (Requests)

All eBay Browse API endpoints are implemented as dedicated Request classes:

| Request Class | Endpoint | Description |
|--------------|----------|-------------|
| `SearchItemsRequest` | `GET /item_summary/search` | Search for items by keyword, category, etc. |
| `GetItemRequest` | `GET /item/{itemId}` | Get full item details |
| `SearchByImageRequest` | `POST /item_summary/search_by_image` | Visual search using image |
| `GetItemByLegacyIdRequest` | `GET /item/get_item_by_legacy_id` | Get item by old numeric ID |
| `GetItemsByItemGroupRequest` | `GET /item/get_items_by_item_group` | Get all variations in a group |
| `CheckCompatibilityRequest` | `POST /item/{itemId}/check_compatibility` | Check automotive compatibility |

### Adding New Endpoints

To add support for a new eBay Browse endpoint:

1. **Create a Request class** in `Contexts/Default/Requests/`:

```php
final class NewEndpointRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $param1,
        private readonly ?string $param2 = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/buy/browse/v1/your-endpoint';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'param1' => $this->param1,
            'param2' => $this->param2,
        ], fn($v) => $v !== null);
    }
}
```

2. **Create an Action** (optional, for business logic):

```php
final class NewFeatureAction
{
    public function __construct(private RestGatewayContract $gateway) {}

    public function execute(string $param1): Result
    {
        $request = new NewEndpointRequest($param1);
        return $this->gateway->execute($request);
    }
}
```

That's it! No need to modify the adapter or gateway.

## Configuration

Add to your `.env`:

```env
EBAY_ACCESS_TOKEN=your_access_token
EBAY_MARKETPLACE_ID=EBAY_US
EBAY_SANDBOX=false
```

Or configure in `config/services.php`:

```php
'ebay' => [
    'access_token' => env('EBAY_ACCESS_TOKEN'),
    'marketplace_id' => env('EBAY_MARKETPLACE_ID', 'EBAY_US'),
    'sandbox' => env('EBAY_SANDBOX', false),
],
```

## Testing

### Unit Testing Actions

```php
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\SearchItemsAction;
use Plenipotentiary\Laravel\Support\Result;

test('search action returns results', function () {
    $gateway = Mockery::mock(RestGatewayContract::class);
    $gateway->shouldReceive('execute')
        ->once()
        ->andReturn(Result::ok(['itemSummaries' => []]));

    $action = new SearchItemsAction($gateway);
    $result = $action->execute('laptop');

    expect($result->isOk())->toBeTrue();
});
```

### Integration Testing

```php
test('can search eBay items', function () {
    $response = $this->postJson('/api/ebay/search', [
        'q' => 'laptop',
        'limit' => 20,
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

## Why This Pattern?

### ❌ What We're NOT Doing

```php
// BAD: Tight coupling to eBay SDK
use eBay\SDK\BrowseAPI;

$client = new BrowseAPI($config);
$response = $client->searchItems(['q' => 'laptop']);
// Now your domain knows about eBay!
```

### ✅ What We ARE Doing

```php
// GOOD: Domain depends on stable interface
public function __construct(private RestGatewayContract $gateway) {}

$request = new SearchItemsRequest('laptop');
$result = $this->gateway->execute($request);
// Domain is provider-agnostic!
```

### Benefits

1. **Testability**: Mock the gateway, not eBay
2. **Portability**: Switch providers without touching domain code
3. **Cross-Cutting Concerns**: Gateway handles logging, retries, idempotency
4. **Type Safety**: Request classes provide IDE autocomplete and type checking
5. **Discoverability**: `Requests/` folder is a catalog of all supported endpoints
6. **Scalability**: Adding endpoints doesn't modify existing code (Open/Closed Principle)

## Related Documentation

- [PATTERN_DECISION_GUIDE.md](../../docs/PATTERN_DECISION_GUIDE.md) - When to use which pattern
- [NON_CRUD_OPERATIONS_PATTERN.md](../../docs/NON_CRUD_OPERATIONS_PATTERN.md) - Non-CRUD pattern details
- [AGENTS.md](../../AGENTS.md) - Package-level architecture guidelines

## Support

For issues, questions, or contributions, please refer to the main package documentation.
