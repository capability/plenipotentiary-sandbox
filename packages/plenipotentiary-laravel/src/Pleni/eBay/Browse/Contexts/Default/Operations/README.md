# eBay Browse API - Operation Pattern

This directory contains **Operation Pattern** implementations for the eBay Browse API.

## Why the Operation Pattern?

The eBay Browse API is **not resource-based** (no full CRUD lifecycle). Instead, it's an **action/query-based API** focused on:
- Searching for items
- Retrieving item details
- Searching by image
- Checking compatibility

Since these are **operations** rather than resources, the Operation Pattern is the right choice.

## Pattern Structure

Each operation follows this structure:

```
Operations/
└── {OperationName}/
    ├── {OperationName}Operation.php  ← Input specification (validated, serializable)
    ├── {OperationName}Gateway.php    ← Stable execution interface
    └── {OperationName}DTO.php        ← Output specification (structured results)
```

## Example: SearchItems

### 1. Operation (Input)
```php
// Represents "what" we want to do
$operation = new SearchItemsOperation(
    query: 'laptop',
    limit: 50,
    filter: 'price:[100..500]',
    categoryIds: '58058'
);

// Validates on construction
// Serializable for queuing
// Type-safe
```

### 2. Gateway (Execution)
```php
// Stable interface for executing the operation
$gateway = new SearchItemsGateway($restGateway);

// Handles:
// - Translation to eBay-specific request
// - Execution via REST gateway
// - Cross-cutting concerns (logging, monitoring)
$result = $gateway->execute($operation);
```

### 3. DTO (Output)
```php
// Structured, type-safe output
if ($result->isOk()) {
    $dto = $result->unwrap();
    
    echo "Found {$dto->total} items\n";
    echo "Page {$dto->getCurrentPage()} of {$dto->getTotalPages()}\n";
    
    if ($dto->hasMoreResults()) {
        echo "Next offset: {$dto->getNextOffset()}\n";
    }
    
    // Convenience methods
    $priceRange = $dto->getPriceRange();
    $itemIds = $dto->getItemIds();
}
```

## Developer Integration

### In Controllers
```php
class ItemSearchController extends Controller
{
    public function search(Request $request, SearchItemsAction $action): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string',
            'limit' => 'integer|min:1|max:200',
        ]);
        
        // Build operation from request
        $operation = SearchItemsOperation::fromArray($validated);
        
        // Execute via action
        $result = $action->execute($operation);
        
        return response()->json([
            'success' => $result->isOk(),
            'data' => $result->isOk() ? $result->unwrap()->toArray() : null,
        ]);
    }
}
```

### In Jobs
```php
class SyncPriceDataJob implements ShouldQueue
{
    public function __construct(
        private readonly SearchItemsOperation $operation
    ) {}
    
    public function handle(SearchItemsAction $action): void
    {
        $result = $action->execute($this->operation);
        
        if ($result->isOk()) {
            $dto = $result->unwrap();
            // Process results...
        }
    }
}

// Dispatch
SyncPriceDataJob::dispatch(
    new SearchItemsOperation('laptop', limit: 200)
);
```

### In Commands
```php
class SearchItemsCommand extends Command
{
    public function handle(SearchItemsAction $action): int
    {
        $operation = new SearchItemsOperation(
            query: $this->argument('query'),
            limit: (int) $this->option('limit'),
        );
        
        $result = $action->execute($operation);
        
        if ($result->isOk()) {
            $dto = $result->unwrap();
            $this->info("Found {$dto->total} items");
        }
        
        return Command::SUCCESS;
    }
}
```

## Benefits

### Type Safety
- Operation objects validated on construction
- DTOs provide structured output
- IDE autocomplete throughout

### Queueable
- Operations are serializable
- Safe to dispatch to background jobs
- No anonymous closures or dynamic data

### Testable
```php
test('search returns results', function () {
    $operation = new SearchItemsOperation('laptop', limit: 10);
    
    $gateway = Mockery::mock(SearchItemsGateway::class);
    $gateway->shouldReceive('execute')
        ->with($operation)
        ->andReturn(Result::ok(new SearchItemsDTO([...])));
    
    $action = new SearchItemsAction($gateway);
    $result = $action->execute($operation);
    
    expect($result->isOk())->toBeTrue();
});
```

### Maintainable
- Each operation is isolated
- Clear input/output contracts
- Easy to add new operations without modifying existing code

## Adding a New Operation

1. Create operation folder: `Operations/YourOperation/`
2. Create three files:
   - `YourOperation.php` - Input specification
   - `YourOperationGateway.php` - Execution logic
   - `YourOperationDTO.php` - Output specification
3. Create action in `Actions/YourOperationAction.php`
4. Use in controllers, commands, jobs

No need to modify any existing code!

## Comparison with Other Patterns

### vs. CRUD Pattern
- **CRUD**: For resources with full lifecycle (Create/Read/Update/Delete)
- **Operation**: For actions and queries (Search, Generate, Calculate)

### vs. REST Pattern  
- **REST**: Direct Saloon request classes, many endpoints
- **Operation**: Structured operations with validation and DTOs

### vs. Procedure Pattern
- **Procedure**: Simple RPC, quick prototypes
- **Operation**: Type-safe, production-ready operations

## See Also

- `/docs/PATTERN_DECISION_GUIDE.md` - When to use which pattern
- `../Requests/` - eBay-specific Saloon request implementations
- `../Actions/` - Action layer that uses operations
