# eBay Browse API - Operation Pattern Implementation

## Overview

The eBay Browse API implementation demonstrates the **Operation Pattern** for non-CRUD, action/query-based APIs. This pattern provides type safety, validation, and queueability while maintaining clean separation between domain logic and provider-specific implementation details.

## Why Operation Pattern for eBay Browse?

eBay Browse is **not resource-based**. It doesn't have a full CRUD lifecycle. Instead, it's an **action/query-based API**:
- **Search** for items (query)
- **Get** item details (query)
- **Search by image** (action)
- **Check compatibility** (action)

The Operation Pattern is perfect for these use cases because it provides:
- ✅ Type-safe input (Operation objects)
- ✅ Validated parameters (at construction)
- ✅ Queueable operations (serializable)
- ✅ Structured output (DTO objects)
- ✅ Clean separation of concerns

## Architecture

### High-Level Structure

```
eBay/Browse/
├── Contexts/
│   └── Default/
│       ├── Operations/          ← NEW: Type-safe operation definitions
│       │   ├── SearchItems/
│       │   │   ├── SearchItemsOperation.php    (Input)
│       │   │   ├── SearchItemsGateway.php      (Execution)
│       │   │   └── SearchItemsDTO.php          (Output)
│       │   └── GetItemDetails/
│       │       ├── GetItemDetailsOperation.php
│       │       ├── GetItemDetailsGateway.php
│       │       └── GetItemDetailsDTO.php
│       ├── Requests/            ← eBay-specific Saloon requests
│       ├── Actions/             ← Application-layer actions
│       ├── Commands/            ← CLI commands
│       ├── Controllers/         ← HTTP controllers
│       ├── Jobs/                ← Background jobs
│       └── Providers/           ← Service providers
└── Shared/
    ├── Transfer/
    │   ├── Rest/                ← REST gateway & adapter
    │   └── Procedure/           ← RPC gateway & adapter (alternative)
    ├── Auth/                    ← Authentication strategy
    └── Support/                 ← Error mapping, config
```

## Pattern Components

### 1. Operation (Input Specification)

**Purpose:** Define what operation to perform with validated parameters.

**File:** `Operations/SearchItems/SearchItemsOperation.php`

**Characteristics:**
- Immutable (readonly properties)
- Self-validating (throws exceptions on invalid input)
- Serializable (safe for queues)
- Type-safe (no magic arrays)

**Example:**
```php
$operation = new SearchItemsOperation(
    query: 'laptop',
    limit: 50,
    filter: 'price:[100..500]',
    categoryIds: '58058'
);

// Validates on construction:
// - query must not be empty
// - limit must be 1-200
// - offset must be multiple of limit
```

### 2. Gateway (Execution Interface)

**Purpose:** Stable interface for executing operations.

**File:** `Operations/SearchItems/SearchItemsGateway.php`

**Responsibilities:**
- Accept Operation objects
- Translate to provider-specific requests
- Delegate to REST/Procedure gateway
- Handle cross-cutting concerns (logging, idempotency)

**Example:**
```php
$gateway = new SearchItemsGateway($restGateway);
$result = $gateway->execute($operation);

// Gateway handles:
// - Translation: Operation → SearchItemsRequest
// - Execution: via REST gateway
// - Error handling: consistent error mapping
// - Logging: all operations logged
```

### 3. DTO (Output Specification)

**Purpose:** Structured, type-safe output.

**File:** `Operations/SearchItems/SearchItemsDTO.php`

**Characteristics:**
- Decouples domain from provider response format
- Provides convenience methods
- Serializable for caching/storage
- Predictable interface

**Example:**
```php
$dto = SearchItemsDTO::fromApiResponse($apiResponse);

// Structured access
echo "Found {$dto->total} items\n";
echo "Page {$dto->getCurrentPage()} of {$dto->getTotalPages()}\n";

// Convenience methods
$priceRange = $dto->getPriceRange();
$itemIds = $dto->getItemIds();
$newItems = $dto->filterByCondition('NEW');

// Pagination helpers
if ($dto->hasMoreResults()) {
    $nextOffset = $dto->getNextOffset();
}
```

## Data Flow

```
Controller/Command/Job
       ↓
   1. Build Operation
       ↓
┌─────────────────┐
│    Operation    │ ← Validated input
│ (SearchItems)   │
└─────────┬───────┘
          ↓
   2. Pass to Action
          ↓
┌─────────────────┐
│     Action      │ ← Domain logic
│ (SearchItems)   │
└─────────┬───────┘
          ↓
   3. Execute via Gateway
          ↓
┌─────────────────┐
│     Gateway     │ ← Stable interface
│ (SearchItems)   │
└─────────┬───────┘
          ↓
   4. Translate to Request
          ↓
┌─────────────────┐
│  Saloon Request │ ← eBay-specific
│ (SearchItems)   │
└─────────┬───────┘
          ↓
   5. Execute via REST Gateway
          ↓
┌─────────────────┐
│  REST Gateway   │ ← Generic REST execution
│  REST Adapter   │
└─────────┬───────┘
          ↓
     eBay API
          ↓
   6. Response
          ↓
┌─────────────────┐
│      DTO        │ ← Structured output
│ (SearchItems)   │
└─────────┬───────┘
          ↓
   7. Return to caller
          ↓
Controller/Command/Job
```

## Implementation Examples

### Controller Usage

```php
class ItemSearchController extends Controller
{
    public function search(Request $request, SearchItemsAction $action): JsonResponse
    {
        // 1. Validate Laravel request
        $validated = $request->validate([
            'q' => 'required|string',
            'limit' => 'integer|min:1|max:200',
        ]);
        
        try {
            // 2. Create operation (validates input)
            $operation = SearchItemsOperation::fromArray($validated);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        
        // 3. Execute via action
        $result = $action->execute($operation);
        
        // 4. Handle result
        if ($result->isErr()) {
            return response()->json(['error' => $result->unwrapErr()], 500);
        }
        
        // 5. Return DTO data
        $dto = $result->unwrap();
        return response()->json([
            'success' => true,
            'data' => $dto->toArray(),
        ]);
    }
}
```

### Command Usage

```php
class SearchItemsCommand extends Command
{
    protected $signature = 'ebay:search 
                            {query} 
                            {--limit=20}
                            {--offset=0}';

    public function handle(SearchItemsAction $action): int
    {
        try {
            // 1. Build operation from CLI input
            $operation = new SearchItemsOperation(
                query: $this->argument('query'),
                limit: (int) $this->option('limit'),
                offset: (int) $this->option('offset'),
            );
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
        
        // 2. Execute
        $result = $action->execute($operation);
        
        // 3. Display results
        if ($result->isOk()) {
            $dto = $result->unwrap();
            $this->info("Found {$dto->total} items");
            $this->line("Page {$dto->getCurrentPage()} of {$dto->getTotalPages()}");
            
            // Use DTO methods for display
            foreach ($dto->items as $item) {
                $this->line("• {$item['title']}");
            }
            
            if ($dto->hasMoreResults()) {
                $this->line("\n→ More results available. Use --offset={$dto->getNextOffset()}");
            }
        }
        
        return Command::SUCCESS;
    }
}
```

### Job Usage (Queueable!)

```php
class SyncPriceDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Operation is serializable - safe for queues!
    public function __construct(
        private readonly SearchItemsOperation $operation
    ) {}
    
    public function handle(SearchItemsAction $action): void
    {
        $result = $action->execute($this->operation);
        
        if ($result->isOk()) {
            $dto = $result->unwrap();
            
            // Use DTO convenience methods
            $priceRange = $dto->getPriceRange();
            
            // Store to cache or database
            Cache::put(
                "price_range:{$this->operation->query}",
                $priceRange,
                now()->addHours(1)
            );
        }
    }
}

// Dispatch with type-safe operation
SyncPriceDataJob::dispatch(
    new SearchItemsOperation('laptop', limit: 200)
)->onQueue('price-sync');
```

## Key Benefits

### 1. Type Safety

```php
// ❌ Old way: Magic arrays, no validation
$gateway->call('searchItems', [
    'q' => 'laptop',
    'limt' => 50,  // Typo! Runtime error
]);

// ✅ New way: Type-safe, validated
$operation = new SearchItemsOperation(
    query: 'laptop',
    limit: 50,  // IDE autocomplete, compile-time checks
);
```

### 2. Queueability

```php
// ❌ Can't queue - closure not serializable
dispatch(function() use ($query) {
    $this->searchAction->execute($query, ['limit' => 50]);
});

// ✅ Can queue - operation is serializable
SyncPriceDataJob::dispatch(
    new SearchItemsOperation('laptop', limit: 50)
);
```

### 3. Testability

```php
test('search returns results', function () {
    // 1. Create operation
    $operation = new SearchItemsOperation('laptop', limit: 10);
    
    // 2. Mock gateway
    $mockGateway = Mockery::mock(SearchItemsGateway::class);
    $mockGateway->shouldReceive('execute')
        ->with($operation)
        ->andReturn(Result::ok($expectedDto));
    
    // 3. Test action
    $action = new SearchItemsAction($mockGateway);
    $result = $action->execute($operation);
    
    expect($result->isOk())->toBeTrue();
});
```

### 4. Maintainability

```php
// Adding a new operation:
// 1. Create Operations/NewOperation/ folder
// 2. Add 3 files:
//    - NewOperation.php (input)
//    - NewOperationGateway.php (execution)
//    - NewOperationDTO.php (output)

// No modifications needed to:
// ❌ Existing operations
// ❌ Gateway infrastructure
// ❌ Adapter layer
// ❌ Service providers
```

### 5. Provider Agnostic

```php
// Domain layer only knows about operations
$operation = new SearchItemsOperation('laptop');

// NOT:
// ❌ eBay SDK classes
// ❌ HTTP client details
// ❌ API endpoints
// ❌ Authentication

// All eBay knowledge is in:
// ✅ SearchItemsRequest (Requests/ folder)
// ✅ eBayBrowseRestAdapter (Transfer/ folder)
```

## Files Created/Modified

### Created
- `Operations/SearchItems/SearchItemsOperation.php` ← Input
- `Operations/SearchItems/SearchItemsGateway.php` ← Execution
- `Operations/SearchItems/SearchItemsDTO.php` ← Output
- `Operations/GetItemDetails/GetItemDetailsOperation.php` ← Input
- `Operations/GetItemDetails/GetItemDetailsGateway.php` ← Execution
- `Operations/GetItemDetails/GetItemDetailsDTO.php` ← Output
- `Operations/README.md` ← Documentation

### Modified
- `Actions/SearchItemsAction.php` ← Now uses Operation pattern
- `Actions/GetItemAction.php` ← Now uses Operation pattern
- `Commands/SearchItemsCommand.php` ← Uses Operations & DTOs
- `Commands/GetItemCommand.php` ← Uses Operations & DTOs
- `Controllers/ItemSearchController.php` ← Uses Operations & DTOs

### Existing (Unchanged)
- `Requests/*.php` ← eBay-specific Saloon requests (still used by Gateways)
- `Shared/Transfer/Rest/*` ← REST gateway infrastructure
- `Shared/Transfer/Procedure/*` ← Alternative RPC pattern
- `Shared/Auth/*` ← Authentication
- `Shared/Support/*` ← Utilities

## Comparison with Other Patterns

### vs. CRUD Pattern
| Feature | CRUD | Operation |
|---------|------|-----------|
| **Use Case** | Resource lifecycle (Create/Read/Update/Delete) | Actions & queries |
| **Input** | CanonicalDTO | Operation object |
| **Structure** | CrudAdapter, CrudGateway | Operation, Gateway, DTO |
| **Example** | Campaigns, Customers | Search, Generate, Calculate |
| **Persistence** | Built-in | Optional |
| **Best For** | Full CRUD resources | Non-CRUD APIs |

### vs. REST Pattern
| Feature | REST | Operation |
|---------|------|-----------|
| **Input** | Saloon Request | Operation object |
| **Validation** | Request class | Operation class |
| **Output** | Raw response | Structured DTO |
| **Use Case** | Many diverse endpoints | Structured operations |
| **Best For** | 50+ endpoints | Production operations |

### vs. Procedure Pattern
| Feature | Procedure | Operation |
|---------|-----------|-----------|
| **Input** | String + array | Typed object |
| **Validation** | Runtime | Construction |
| **Type Safety** | Low | High |
| **Queueability** | Limited | Full |
| **Best For** | Prototypes, scripts | Production operations |

## When to Use Operation Pattern

✅ **Use Operation Pattern when:**
- API is action/query-based (not resource-based)
- Need type safety and validation
- Operations will be queued
- Building production applications
- Want structured, predictable output

❌ **Don't use Operation Pattern when:**
- API has full CRUD lifecycle → Use **CRUD Pattern**
- Need to prototype quickly → Use **Procedure Pattern**
- Have 50+ diverse endpoints → Use **REST Pattern**

## Testing Strategy

### Unit Tests (Operation)
```php
test('operation validates input', function () {
    expect(fn() => new SearchItemsOperation('', limit: 0))
        ->toThrow(InvalidArgumentException::class);
});

test('operation accepts valid input', function () {
    $op = new SearchItemsOperation('laptop', limit: 50);
    expect($op->query)->toBe('laptop');
});
```

### Unit Tests (DTO)
```php
test('DTO calculates pagination', function () {
    $dto = new SearchItemsDTO(
        items: [],
        total: 100,
        limit: 50,
        offset: 0
    );
    
    expect($dto->getTotalPages())->toBe(2);
    expect($dto->getCurrentPage())->toBe(1);
    expect($dto->hasMoreResults())->toBeTrue();
    expect($dto->getNextOffset())->toBe(50);
});
```

### Integration Tests (Gateway)
```php
test('gateway executes operation', function () {
    $operation = new SearchItemsOperation('laptop', limit: 10);
    $gateway = app(SearchItemsGateway::class);
    
    $result = $gateway->execute($operation);
    
    expect($result->isOk())->toBeTrue();
});
```

### Feature Tests (Action)
```php
test('action returns DTO', function () {
    $operation = new SearchItemsOperation('test', limit: 1);
    $action = app(SearchItemsAction::class);
    
    $result = $action->execute($operation);
    
    if ($result->isOk()) {
        expect($result->unwrap())->toBeInstanceOf(SearchItemsDTO::class);
    }
});
```

## Next Steps

### 1. Add More Operations
- SearchByImage → Create operation for visual search
- GetItemsByItemGroup → Create operation for variations
- CheckCompatibility → Create operation for compatibility checks

### 2. Add Persistence
- Cache search results using DTO
- Store price history
- Track item changes over time

### 3. Add Monitoring
- Log all operations
- Track success/failure rates
- Monitor response times
- Alert on errors

### 4. Extend DTOs
- Add more convenience methods
- Add data transformation helpers
- Add validation methods

## Summary

The Operation Pattern provides a **structured, type-safe, and maintainable** way to work with action/query-based APIs like eBay Browse. It offers:

✅ **Type safety** through Operation objects  
✅ **Validation** at construction time  
✅ **Queueability** for background processing  
✅ **Structured output** via DTOs  
✅ **Clean separation** between domain and provider  
✅ **Easy testing** with mockable components  
✅ **Maintainability** through isolated operations  

## See Also

- `/docs/PATTERN_DECISION_GUIDE.md` - When to use which pattern
- `AGENTS.md` - Agent-specific guidelines
- `Operations/README.md` - Detailed operation pattern guide
- `Requests/` - eBay-specific Saloon request implementations
