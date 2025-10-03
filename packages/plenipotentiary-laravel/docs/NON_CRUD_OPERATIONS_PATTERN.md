# Non-CRUD Operations Pattern

## The Problem

Not all APIs are resource-based with CRUD operations. Many APIs are **action-based** or **query-based**:
- **eBay Browse API** - Search/query operations (not creating/updating items)
- **OpenAI API** - Generate completions, create embeddings (not managing resources)
- **Stripe Charges** - Create charges, refunds (action-oriented, not full CRUD)
- **Google Ads Reporting** - Query reports (read-only, complex filters)

**The challenge:** How do we maintain the Gateway/Adapter pattern, CanonicalDTO benefits, and Laravel integration WITHOUT forcing everything into CRUD?

---

## The Solution: Operation-Based Pattern

For non-CRUD APIs, we use an **Operation-Based** pattern that mirrors real-world use cases.

### Core Principle

> **Organize by USE CASE, not by HTTP verb or API endpoint.**

Instead of forcing "Create/Read/Update/Delete", we model what developers actually DO:
- ✅ "Search for laptops under $500"
- ✅ "Generate product recommendations"
- ✅ "Verify item availability"
- ❌ "POST to /item_summary/search" (too low-level)

---

## Pattern Structure

```
Pleni/{Provider}/{Domain}/
  ├── Contexts/
  │   ├── Default/              ← Default context (no special behavior)
  │   │   ├── Operations/       ← Business operations (USE CASES)
  │   │   │   ├── SearchItems/
  │   │   │   │   ├── SearchItemsOperation.php
  │   │   │   │   ├── SearchItemsGateway.php
  │   │   │   │   ├── SearchItemsDTO.php
  │   │   │   │   └── SearchItemsResult.php
  │   │   │   └── GetItemDetails/
  │   │   │       ├── GetItemDetailsOperation.php
  │   │   │       ├── GetItemDetailsGateway.php
  │   │   │       └── GetItemDetailsDTO.php
  │   │   ├── Actions/          ← Laravel Actions (application layer)
  │   │   │   ├── SearchItemsAction.php
  │   │   │   └── GetItemDetailsAction.php
  │   │   ├── Commands/         ← Artisan Commands
  │   │   │   └── SearchItemsCommand.php
  │   │   ├── Jobs/             ← Queue Jobs
  │   │   │   └── SyncItemsJob.php
  │   │   └── Providers/
  │   │       └── eBayBrowseServiceProvider.php
  │   │
  │   └── Marketplace/          ← Alternative context (if behavior differs)
  │       └── Operations/
  │           └── SearchMarketplaceItems/
  │
  └── Shared/
      ├── Transfer/Rest/        ← Provider integration
      │   ├── {Provider}RestAdapter.php
      │   ├── {Provider}RestGateway.php
      │   └── {Provider}RestConnector.php
      └── Support/
          ├── {Provider}Config.php
          └── {Provider}ErrorMapper.php
```

---

## Key Components

### 1. Operation (Adapter Layer - Provider-Specific)

The Operation is where **provider-specific logic** lives. It's similar to `CampaignCreate` but for non-CRUD use cases.

```php
// src/Pleni/eBay/Browse/Contexts/Default/Operations/SearchItems/SearchItemsOperation.php

final class SearchItemsOperation implements OperationContract
{
    public const INPUT_SPEC = [
        'query' => ['rules' => ['required', 'string', 'min:2']],
        'limit' => ['rules' => ['integer', 'min:1', 'max:200'], 'default' => 50],
        'categoryIds' => ['rules' => ['array']],
        'priceMin' => ['rules' => ['numeric', 'min:0']],
        'priceMax' => ['rules' => ['numeric']],
        'condition' => ['rules' => ['in:NEW,USED,REFURBISHED']],
        'sort' => ['rules' => ['in:price,distance,newlyListed']],
    ];

    public function __construct(
        private eBayBrowseRestConnector $connector,
        private eBayErrorMapper $errorMapper,
        private LoggerInterface $logger,
    ) {}

    public static function inputSpec(): array
    {
        return self::INPUT_SPEC;
    }

    /**
     * Execute the search operation
     */
    public function perform(SearchItemsDTO $dto): Result
    {
        try {
            $this->logger->info('eBay: Searching items', [
                'query' => $dto->query,
                'limit' => $dto->limit,
            ]);

            $request = $this->buildRequest($dto);
            $response = $this->connector->send($request);

            if (!$response->successful()) {
                return $this->handleError($response);
            }

            // Map response to result DTO
            $result = $this->mapResponse($response->json(), $dto);
            
            return Result::ok($result);
            
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    private function buildRequest(SearchItemsDTO $dto): Request
    {
        return new class($dto) extends Request {
            protected Method $method = Method::GET;
            
            public function __construct(private SearchItemsDTO $dto) {}

            public function resolveEndpoint(): string
            {
                return '/buy/browse/v1/item_summary/search';
            }

            protected function defaultQuery(): array
            {
                $query = [
                    'q' => $this->dto->query,
                    'limit' => $this->dto->limit,
                ];

                if ($this->dto->categoryIds) {
                    $query['category_ids'] = implode(',', $this->dto->categoryIds);
                }

                // Build filter parameter
                $filters = [];
                if ($this->dto->priceMin || $this->dto->priceMax) {
                    $min = $this->dto->priceMin ?? '';
                    $max = $this->dto->priceMax ?? '';
                    $filters[] = "price:[{$min}..{$max}]";
                }
                if ($this->dto->condition) {
                    $filters[] = "conditionIds:{$this->dto->condition}";
                }
                if ($filters) {
                    $query['filter'] = implode(',', $filters);
                }

                if ($this->dto->sort) {
                    $query['sort'] = $this->dto->sort;
                }

                return array_filter($query);
            }
        };
    }

    private function mapResponse(array $data, SearchItemsDTO $request): SearchItemsResult
    {
        $items = array_map(
            fn($item) => ItemSummaryDTO::fromArray($item),
            $data['itemSummaries'] ?? []
        );

        return new SearchItemsResult(
            items: $items,
            total: $data['total'] ?? 0,
            limit: $data['limit'] ?? $request->limit,
            offset: $data['offset'] ?? 0,
            query: $request->query,
        );
    }
}
```

### 2. Gateway (Stable Layer - Cross-Cutting Concerns)

The Gateway provides logging, policy enforcement, idempotency, etc.

```php
// src/Pleni/eBay/Browse/Contexts/Default/Operations/SearchItems/SearchItemsGateway.php

final class SearchItemsGateway
{
    public function __construct(
        private SearchItemsOperation $operation,
        private LoggerInterface $logger,
        private GatewayPolicyChain $policyChain,
    ) {}

    public function search(SearchItemsDTO $dto): Result
    {
        $this->logger->info('Gateway: Searching items', [
            'query' => $dto->query,
        ]);

        $call = new GatewayCall('ebay.browse.searchItems', $dto->toArray());

        return $this->policyChain->invoke(
            fn() => $this->operation->perform($dto),
            $call
        );
    }
}
```

### 3. DTO (Data Transfer Objects)

**Input DTO** - What the developer provides:

```php
// src/Pleni/eBay/Browse/Contexts/Default/Operations/SearchItems/SearchItemsDTO.php

final class SearchItemsDTO
{
    public function __construct(
        public readonly string $query,
        public readonly int $limit = 50,
        public readonly ?array $categoryIds = null,
        public readonly ?float $priceMin = null,
        public readonly ?float $priceMax = null,
        public readonly ?string $condition = null,
        public readonly ?string $sort = null,
        public readonly int $offset = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            query: $data['query'],
            limit: $data['limit'] ?? 50,
            categoryIds: $data['categoryIds'] ?? null,
            priceMin: $data['priceMin'] ?? null,
            priceMax: $data['priceMax'] ?? null,
            condition: $data['condition'] ?? null,
            sort: $data['sort'] ?? null,
            offset: $data['offset'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'query' => $this->query,
            'limit' => $this->limit,
            'categoryIds' => $this->categoryIds,
            'priceMin' => $this->priceMin,
            'priceMax' => $this->priceMax,
            'condition' => $this->condition,
            'sort' => $this->sort,
            'offset' => $this->offset,
        ], fn($v) => $v !== null);
    }
}
```

**Result DTO** - What gets returned:

```php
// src/Pleni/eBay/Browse/Contexts/Default/Operations/SearchItems/SearchItemsResult.php

final class SearchItemsResult
{
    public function __construct(
        public readonly array $items,      // Array of ItemSummaryDTO
        public readonly int $total,
        public readonly int $limit,
        public readonly int $offset,
        public readonly string $query,
        public readonly ?string $nextPageToken = null,
    ) {}

    public function hasMore(): bool
    {
        return ($this->offset + $this->limit) < $this->total;
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
            'total' => $this->total,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'query' => $this->query,
            'hasMore' => $this->hasMore(),
        ];
    }
}

// Individual item DTO
final class ItemSummaryDTO
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $title,
        public readonly string $price,
        public readonly string $currency,
        public readonly ?string $imageUrl = null,
        public readonly ?string $condition = null,
        public readonly ?string $seller = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            itemId: $data['itemId'],
            title: $data['title'],
            price: $data['price']['value'] ?? '0',
            currency: $data['price']['currency'] ?? 'USD',
            imageUrl: $data['image']['imageUrl'] ?? null,
            condition: $data['condition'] ?? null,
            seller: $data['seller']['username'] ?? null,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
```

### 4. Action (Laravel Application Layer)

Actions are where **developers write their business logic**.

```php
// src/Pleni/eBay/Browse/Contexts/Default/Actions/SearchItemsAction.php

use Lorisleiva\Actions\Concerns\AsAction;

final class SearchItemsAction
{
    use AsAction;

    public function __construct(
        private SearchItemsGateway $gateway,
    ) {}

    /**
     * Handle the search
     */
    public function handle(string $query, array $options = []): Result
    {
        $dto = SearchItemsDTO::fromArray([
            'query' => $query,
            'limit' => $options['limit'] ?? 50,
            'categoryIds' => $options['categories'] ?? null,
            'priceMin' => $options['price_min'] ?? null,
            'priceMax' => $options['price_max'] ?? null,
            'condition' => $options['condition'] ?? null,
            'sort' => $options['sort'] ?? null,
        ]);

        return $this->gateway->search($dto);
    }

    /**
     * As a controller
     */
    public function asController(Request $request): JsonResponse
    {
        $result = $this->handle(
            $request->input('query'),
            $request->only(['limit', 'categories', 'price_min', 'price_max', 'condition', 'sort'])
        );

        if ($result->isOk()) {
            return response()->json($result->value()->toArray());
        }

        return response()->json(['error' => $result->error()], 400);
    }

    /**
     * As a job
     */
    public function asJob(string $query, array $options = []): void
    {
        $result = $this->handle($query, $options);
        
        if ($result->isOk()) {
            // Store results, trigger events, etc.
            event(new ItemsSearched($result->value()));
        }
    }

    /**
     * As a command
     */
    public function asCommand(Command $command): int
    {
        $query = $command->argument('query');
        $limit = $command->option('limit') ?? 10;

        $command->info("Searching for: {$query}");

        $result = $this->handle($query, ['limit' => $limit]);

        if ($result->isErr()) {
            $command->error('Search failed: ' . $result->error()['message']);
            return Command::FAILURE;
        }

        $searchResult = $result->value();
        $command->info("Found {$searchResult->total} items");

        foreach ($searchResult->items as $item) {
            $command->line("- {$item->title} ({$item->price} {$item->currency})");
        }

        return Command::SUCCESS;
    }
}
```

---

## Real-World Use Cases

### Use Case 1: E-commerce Product Search

**Developer writes:**

```php
// In a controller
class ProductController extends Controller
{
    public function search(Request $request, SearchItemsAction $action)
    {
        return $action->handle(
            query: $request->input('q'),
            options: [
                'limit' => 20,
                'price_max' => 500,
                'condition' => 'NEW',
                'sort' => 'price',
            ]
        );
    }
}
```

### Use Case 2: Scheduled Price Monitoring

**Developer writes:**

```php
// In a scheduled job
class MonitorPricesJob implements ShouldQueue
{
    public function handle(SearchItemsAction $action)
    {
        $keywords = ['laptop', 'macbook', 'thinkpad'];

        foreach ($keywords as $keyword) {
            $result = $action->handle($keyword, [
                'limit' => 100,
                'price_max' => 1000,
            ]);

            if ($result->isOk()) {
                // Store prices in database
                $this->storePrices($result->value());
            }
        }
    }
}
```

### Use Case 3: CLI Tool

**Developer writes:**

```php
// Artisan command
php artisan ebay:search "gaming laptop" --limit=10 --price-max=800
```

---

## Benefits Over Pure Procedure/REST

| Aspect | Procedure Pattern | Operation Pattern |
|--------|------------------|-------------------|
| **Organization** | Flat gateway methods | Organized by use case |
| **DTOs** | Raw arrays | Typed input/output DTOs |
| **Validation** | Manual | INPUT_SPEC + DTOs |
| **Discoverability** | Hard to find operations | Clear Operations/ folder |
| **Testing** | Mock entire gateway | Mock specific operations |
| **Documentation** | Lives separate | Self-documenting DTOs |
| **IDE Support** | Weak (arrays) | Strong (types) |
| **Persistence** | No pattern | Clear Result → Repository |

---

## Idempotency & Persistence

### Idempotency for Search Operations

```php
final class SearchItemsGateway
{
    public function __construct(
        private IdempotencyStore $idempotency,
    ) {}

    public function search(SearchItemsDTO $dto, ?string $idempotencyKey = null): Result
    {
        if ($idempotencyKey) {
            $cached = $this->idempotency->get($idempotencyKey);
            if ($cached) {
                return Result::ok($cached);
            }
        }

        $result = $this->operation->perform($dto);

        if ($result->isOk() && $idempotencyKey) {
            $this->idempotency->put($idempotencyKey, $result->value(), ttl: 3600);
        }

        return $result;
    }
}
```

### Persistence Pattern

```php
// After getting results
$result = $searchAction->handle('laptop');

if ($result->isOk()) {
    $searchResult = $result->value();
    
    // Persist to database
    SearchHistory::create([
        'query' => $searchResult->query,
        'total_results' => $searchResult->total,
        'results' => $searchResult->toArray(),
        'searched_at' => now(),
    ]);
    
    // Or store individual items
    foreach ($searchResult->items as $item) {
        ProductCache::updateOrCreate(
            ['external_id' => $item->itemId],
            [
                'title' => $item->title,
                'price' => $item->price,
                'currency' => $item->currency,
                'last_seen' => now(),
            ]
        );
    }
}
```

---

## When to Use Each Pattern

| Pattern | Use Case | Example |
|---------|----------|---------|
| **CRUD** | Managing resources with lifecycle | Campaigns, Customers, Products |
| **Operation** | Actions/queries without full CRUD | Search, Generate, Verify, Calculate |
| **Procedure** | Simple one-off actions | Quick prototypes, admin tools |
| **REST** | Complex type-safe endpoints | APIs with many endpoints |

---

## Context Usage

Use contexts when behavior **differs significantly**:

```
Contexts/
  ├── Default/           ← Standard marketplace search
  │   └── Operations/
  │       └── SearchItems/
  │
  ├── Motors/            ← Car parts have different fields
  │   └── Operations/
  │       └── SearchParts/
  │
  └── RealEstate/        ← Property search needs location
      └── Operations/
          └── SearchProperties/
```

**If no context needed:** Everything goes in `Contexts/Default/`.

---

## Summary

### Key Principles

1. ✅ **Operations, not verbs** - `SearchItems`, not `GET /search`
2. ✅ **Use DTOs** - Type-safe input/output
3. ✅ **Gateway for cross-cutting** - Logging, policies, idempotency
4. ✅ **Operation for provider logic** - API-specific code
5. ✅ **Action for business logic** - Laravel integration
6. ✅ **Context only when needed** - Default is fine for most cases

### The Developer Experience

**Developer wants:** "Search eBay for laptops under $500"

**Developer writes:**
```php
$result = $searchAction->handle('laptop', ['price_max' => 500]);
```

**Not:**
```php
$result = $gateway->call('searchItems', [
    'q' => 'laptop',
    'filter' => 'price:[..500]',
    'limit' => 50,
]); // What fields? What format? What does it return?
```

The Operation pattern gives structure, types, validation, and clear contracts while staying flexible for non-CRUD APIs!
