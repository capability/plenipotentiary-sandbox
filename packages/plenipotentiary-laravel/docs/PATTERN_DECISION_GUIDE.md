# Pattern Decision Guide

Quick reference for choosing the right pattern for your API integration.

## Decision Tree

```
Is it a resource with full lifecycle (Create/Read/Update/Delete)?
├─ YES → Use CRUD Pattern
│         Examples: Campaigns, Customers, Products, Orders
│
└─ NO → Is it an action/query operation?
          ├─ Simple, one-off → Use Procedure Pattern
          │                   Examples: Quick scripts, admin tools
          │
          └─ Complex, reusable → Use Operation Pattern
                                Examples: Search, Generate, Verify
```

---

## Pattern Comparison

### 1. CRUD Pattern (Resource-Based)

**When:** Managing resources with Create/Read/Update/Delete lifecycle

**Structure:**
```
Contexts/{Context}/{Resource}/
  ├── Adapter/
  │   ├── {Resource}CrudAdapter.php
  │   ├── {Resource}Create.php
  │   ├── {Resource}Update.php
  │   ├── {Resource}Delete.php
  │   └── {Resource}Read.php
  ├── Gateway/
  │   └── {Resource}CrudGateway.php
  ├── DTO/
  │   └── {Resource}CanonicalDTO.php
  └── Selector/
      └── {Resource}Selector.php
```

**Developer writes:**
```php
$campaign = CampaignCanonicalDTO::fromArray([...]);
$result = $gateway->create($campaign);
```

**Examples:**
- Google Ads Campaigns
- Stripe Customers
- Shopify Products

---

### 2. Operation Pattern (Action/Query-Based)

**When:** Non-CRUD operations like search, generate, verify, calculate

**Structure:**
```
Contexts/Default/
  ├── Operations/
  │   └── {UseCase}/
  │       ├── {UseCase}Operation.php
  │       ├── {UseCase}Gateway.php
  │       ├── {UseCase}DTO.php
  │       └── {UseCase}Result.php
  ├── Actions/
  │   └── {UseCase}Action.php
  ├── Commands/
  └── Jobs/
```

**Developer writes:**
```php
$dto = SearchItemsDTO::fromArray([...]);
$result = $searchAction->handle('laptop', ['price_max' => 500]);
```

**Examples:**
- eBay Browse Search
- OpenAI Completions
- Google Ads Reporting
- Price calculators

---

### 3. Procedure Pattern (Simple RPC)

**When:** Quick prototypes, simple one-off operations

**Structure:**
```
Shared/Transfer/Procedure/
  ├── {Provider}ProcedureAdapter.php
  ├── {Provider}ProcedureGateway.php
  └── {Provider}ProcedureConnector.php
```

**Developer writes:**
```php
$result = $gateway->call('searchItems', [
    'q' => 'laptop',
    'limit' => 50,
]);
```

**Examples:**
- Admin tools
- Quick scripts
- Rapid prototyping

---

### 4. REST Pattern (Dedicated Requests)

**When:** Many endpoints, need type-safe dedicated classes

**Structure:**
```
Contexts/{Context}/Requests/
  ├── SearchItemsRequest.php
  └── GetItemDetailsRequest.php
  
Shared/Transfer/Rest/
  ├── {Provider}RestAdapter.php
  ├── {Provider}RestGateway.php
  └── {Provider}RestConnector.php
```

**Developer writes:**
```php
$request = new SearchItemsRequest('laptop', limit: 20);
$result = $gateway->execute($request);
```

**Examples:**
- APIs with 50+ endpoints
- Complex request configuration
- Need per-endpoint type safety

---

## Feature Matrix

| Feature | CRUD | Operation | Procedure | REST |
|---------|------|-----------|-----------|------|
| **Type Safety** | ✅✅✅ | ✅✅✅ | ✅ | ✅✅✅ |
| **Validation** | ✅✅✅ | ✅✅✅ | ✅ | ✅✅ |
| **Discoverability** | ✅✅✅ | ✅✅✅ | ✅ | ✅✅ |
| **Ease of Setup** | ✅ | ✅✅ | ✅✅✅ | ✅✅ |
| **Persistence** | ✅✅✅ | ✅✅ | ✅ | ✅✅ |
| **Idempotency** | ✅✅✅ | ✅✅✅ | ✅ | ✅✅ |
| **Laravel Integration** | ✅✅✅ | ✅✅✅ | ✅✅ | ✅✅ |
| **IDE Support** | ✅✅✅ | ✅✅✅ | ✅ | ✅✅✅ |

---

## Real-World Examples

### eBay Browse API

**API Type:** Query/Search-based (not resource management)

**Right Pattern:** ✅ Operation Pattern

**Why:**
- Multiple search operations (by keyword, category, image)
- Each search has different parameters
- Results need to be cached/persisted
- Used in controllers, jobs, commands
- Not managing item lifecycle

**Structure:**
```
eBay/Browse/Contexts/Default/
  ├── Operations/
  │   ├── SearchItems/
  │   ├── GetItemDetails/
  │   └── SearchByImage/
  └── Actions/
      ├── SearchItemsAction.php
      └── GetItemDetailsAction.php
```

---

### OpenAI API

**API Type:** Generation/Completion (action-based)

**Right Pattern:** ✅ Operation Pattern

**Why:**
- Generate completions (not CRUD)
- Create embeddings (action, not resource)
- Complex configuration per use case
- Results need persistence
- Used across application

**Structure:**
```
OpenAI/Api/Contexts/Default/
  ├── Operations/
  │   ├── CreateCompletion/
  │   ├── CreateEmbedding/
  │   └── CreateImage/
  └── Actions/
      ├── GenerateTextAction.php
      └── GenerateEmbeddingAction.php
```

---

### Stripe Customers

**API Type:** Resource management (full lifecycle)

**Right Pattern:** ✅ CRUD Pattern

**Why:**
- Can create, read, update, delete customers
- Clear resource lifecycle
- Need to persist locally
- Classic resource management

**Structure:**
```
Stripe/Api/Contexts/Billing/Customer/
  ├── Adapter/
  │   ├── CustomerCrudAdapter.php
  │   ├── CustomerCreate.php
  │   ├── CustomerUpdate.php
  │   └── CustomerRead.php
  └── Gateway/
      └── CustomerCrudGateway.php
```

---

### Google Ads Campaigns

**API Type:** Resource management with complex contexts

**Right Pattern:** ✅ CRUD Pattern

**Why:**
- Full lifecycle (create, update, delete, read)
- Different contexts (Search, Display, Shopping)
- Need local persistence
- Complex resource with many fields

**Structure:**
```
Google/Ads/Contexts/
  ├── Search/Campaign/
  │   ├── Adapter/
  │   └── Gateway/
  ├── Display/Campaign/
  │   ├── Adapter/
  │   └── Gateway/
  └── Shopping/Campaign/
      ├── Adapter/
      └── Gateway/
```

---

## When to Use Context

### Always Use Contexts

Even if you only have one context, use `Contexts/Default/`:

✅ **Good:**
```
eBay/Browse/Contexts/Default/Operations/SearchItems/
```

❌ **Bad:**
```
eBay/Browse/Operations/SearchItems/
```

**Why:** Consistency, future-proofing, clear organization

### Multiple Contexts

Use multiple contexts when behavior **differs significantly**:

**eBay Motors (different fields):**
```
Contexts/
  ├── Default/       ← General marketplace
  └── Motors/        ← Car-specific fields
```

**Google Ads (different campaign types):**
```
Contexts/
  ├── Search/        ← Search campaigns
  ├── Display/       ← Display campaigns
  └── Shopping/      ← Shopping campaigns
```

**Don't create contexts for:** Minor variations, optional fields, simple filters

---

## Pattern Combinations

You can use multiple patterns in the same provider!

### Google Ads Example

```
Google/Ads/
  ├── Contexts/
  │   └── Search/
  │       └── Campaign/         ← CRUD Pattern
  ├── Reporting/
  │   └── Operations/           ← Operation Pattern
  │       └── GenerateReport/
  └── Shared/
      ├── Transfer/Procedure/   ← Procedure Pattern (for ad-hoc)
      └── Transfer/Rest/        ← REST Pattern (for complex endpoints)
```

**Use each where it fits!**

---

## Migration Path

### Starting Point: Procedure

```php
// Quick prototype
$result = $gateway->call('searchItems', ['q' => 'laptop']);
```

### Evolution: Operation Pattern

```php
// Production-ready
$dto = SearchItemsDTO::fromArray(['query' => 'laptop']);
$result = $searchAction->handle($dto);
```

**When to migrate:** When you need type safety, validation, persistence, or reusability

---

## Summary

### Choose CRUD When:
- ✅ Resource has full lifecycle (CRUD)
- ✅ Need local persistence
- ✅ Clear resource boundaries
- Examples: Customers, Products, Campaigns

### Choose Operation When:
- ✅ Action/query-based API
- ✅ Multiple use cases
- ✅ Need type safety + validation
- ✅ Laravel integration (controllers, jobs, commands)
- Examples: Search, Generate, Verify, Calculate

### Choose Procedure When:
- ✅ Quick prototypes
- ✅ Simple one-off operations
- ✅ Admin tools
- Examples: Scripts, quick tests

### Choose REST When:
- ✅ Many diverse endpoints
- ✅ Need dedicated request classes
- ✅ Complex per-endpoint configuration
- Examples: 50+ endpoint APIs

**Most non-CRUD APIs should use the Operation Pattern!**
