# Plenipotentiary Pattern Documentation

Comprehensive guide to choosing and implementing patterns in Plenipotentiary.

---

## Quick Start

**New to Plenipotentiary?** Read in this order:

1. **[Pattern Decision Guide](./PATTERN_DECISION_GUIDE.md)** - Which pattern should I use?
2. **[Non-CRUD Operations Pattern](./NON_CRUD_OPERATIONS_PATTERN.md)** - Deep dive into Operations
3. **[MCP Pattern](./MCP_PATTERN.md)** - AI agents and Model Context Protocol

---

## Available Patterns

### 🔄 CRUD Pattern
**For:** Resource management with full lifecycle

Resources you create, read, update, and delete:
- Google Ads Campaigns
- Stripe Customers
- Shopify Products
- Database records with CRUD operations

**Key Features:**
- ✅ Full lifecycle management
- ✅ Local persistence via Repository pattern
- ✅ Clear resource boundaries
- ✅ CanonicalDTO for provider-agnostic data

**Example:**
```php
$campaign = CampaignCanonicalDTO::fromArray([...]);
$result = $campaignGateway->create($campaign);
```

---

### ⚡ Operation Pattern
**For:** Action/query-based operations without full CRUD

Operations you perform but don't manage lifecycle:
- eBay item search
- OpenAI completions
- Payment processing
- Report generation
- Price calculations

**Key Features:**
- ✅ Organized by use case, not HTTP verb
- ✅ Type-safe input/output DTOs
- ✅ Laravel Actions integration
- ✅ Perfect for controllers, jobs, commands

**Example:**
```php
$result = $searchAction->handle('laptop', ['price_max' => 500]);
```

**Read more:** [Non-CRUD Operations Pattern](./NON_CRUD_OPERATIONS_PATTERN.md)

---

### 🤖 MCP Pattern
**For:** AI agents using Model Context Protocol

Agents that need local resource access:
- File system operations
- Database queries
- Code search/analysis
- Multi-step agent workflows

**Key Features:**
- ✅ Budget tracking per agent
- ✅ Rate limiting to prevent runaway loops
- ✅ Complete audit trail
- ✅ Idempotency for agent safety
- ✅ Error recovery and retry logic
- ✅ Works alongside CRUD/REST patterns

**Example:**
```php
$result = $callToolAction->handle(
    server: 'filesystem',
    tool: 'read_file',
    arguments: ['path' => $logPath],
    agentId: 'log-analyzer'
);
```

**Read more:** [MCP Pattern](./MCP_PATTERN.md)

---

### 🚀 Procedure Pattern
**For:** Quick prototypes and simple operations

When you need to move fast:
- Admin tools
- One-off scripts
- Rapid prototyping
- Simple integrations

**Key Features:**
- ✅ Fastest to implement
- ✅ Dynamic operation names
- ✅ Good for exploration

**Example:**
```php
$result = $gateway->call('searchItems', ['q' => 'laptop']);
```

**Migration path:** Start here, evolve to Operation pattern when needed

---

### 🌐 REST Pattern
**For:** APIs with many endpoints needing type-safe classes

When you have lots of endpoints:
- 50+ endpoint APIs
- Complex per-endpoint configuration
- Need dedicated request classes

**Key Features:**
- ✅ One class per endpoint
- ✅ Strong type safety
- ✅ Great IDE support

**Example:**
```php
$request = new SearchItemsRequest('laptop', limit: 20);
$result = $gateway->execute($request);
```

---

## Decision Matrix

| Question | Pattern |
|----------|---------|
| Building an AI agent? | **MCP Pattern** |
| Managing resource lifecycle (CRUD)? | **CRUD Pattern** |
| Search/query/action without CRUD? | **Operation Pattern** |
| Quick prototype/script? | **Procedure Pattern** |
| 50+ diverse endpoints? | **REST Pattern** |

---

## Core Concepts

### Gateway/Adapter Separation

All patterns follow this principle:

**Gateway (Stable Layer)**
- Cross-cutting concerns
- Logging, idempotency, retries
- Error mapping
- Policy chain (rate limiting, budget tracking)
- Your application's stable API

**Adapter (Provider Layer)**
- Provider-specific implementation
- SDK calls or HTTP requests
- Request/response mapping
- Understands vendor API quirks

### Result Pattern

All patterns return `Result<T>`:

```php
$result = $gateway->call(...);

if ($result->isOk()) {
    $data = $result->unwrap();
    // Success path
}

if ($result->isErr()) {
    $error = $result->error();
    // Error handling
}

if ($result->isInvalid()) {
    $violations = $result->violations();
    // Validation errors
}
```

### CanonicalDTO

Provider-agnostic data structures:

```php
// Works with Google Ads SDK
$dto = CampaignCanonicalDTO::fromArray([...]);

// Works with different storage
$eloquentRepo->save($dto);
$mongoRepo->save($dto);
$memoryRepo->save($dto);

// Provider context keeps vendor-specific data
$dto->providerContext = ['google.customerId' => '123'];
```

### Policy Chain

All gateways support policies:

```php
class MyGateway {
    public function __construct(
        private GatewayPolicyChain $policyChain,
    ) {}

    public function execute($dto) {
        return $this->policyChain->invoke(
            fn() => $this->operation->perform($dto),
            $call
        );
    }
}
```

**Built-in Policies:**
- `LoggingPolicy` - Audit trail
- `RetryBackoffPolicy` - Automatic retries
- `RateLimitPolicy` - Rate limiting
- `MetricsPolicy` - Observability
- `AgentBudgetPolicy` - Agent cost tracking (MCP only)
- `AgentAuditPolicy` - Agent action logging (MCP only)

---

## Pattern Combinations

**You can mix patterns!** Use each where it fits best.

### Example: Campaign Optimization Agent

```php
class CampaignOptimizerAgent
{
    public function __construct(
        private CallToolAction $mcpTool,          // MCP for analytics
        private GoogleAdsCrudGateway $adsGateway, // CRUD for campaigns
        private StripeGateway $billingGateway,    // REST for billing
    ) {}

    public function optimize(int $campaignId): void
    {
        // 1. Read performance via MCP
        $perf = $this->mcpTool->handle('analytics', 'query', [...]);

        // 2. Analyze with LLM via MCP
        $analysis = $this->mcpTool->handle('openai', 'analyze', [...]);

        // 3. Update campaign via CRUD
        $this->adsGateway->update($campaignDto);

        // 4. Bill customer via REST
        $this->billingGateway->createInvoice($invoiceDto);
    }
}
```

---

## File Structure

### CRUD Pattern
```
Pleni/{Provider}/{Domain}/Contexts/{Context}/{Resource}/
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
  └── Repository/
      └── {Resource}RepositoryContract.php
```

### Operation Pattern
```
Pleni/{Provider}/{Domain}/Contexts/Default/
  ├── Operations/
  │   └── {UseCase}/
  │       ├── {UseCase}Operation.php
  │       ├── {UseCase}Gateway.php
  │       ├── {UseCase}DTO.php
  │       └── {UseCase}Result.php
  └── Actions/
      └── {UseCase}Action.php
```

### MCP Pattern
```
Pleni/MCP/Contexts/Default/
  ├── Operations/
  │   └── CallTool/
  │       ├── CallToolOperation.php
  │       ├── CallToolGateway.php
  │       ├── CallToolDTO.php
  │       └── CallToolResult.php
  ├── Actions/
  │   └── CallToolAction.php
  └── Policies/
      ├── AgentBudgetPolicy.php
      ├── AgentRateLimitPolicy.php
      └── AgentAuditPolicy.php
```

---

## Developer Workflow

The recommended workflow for all patterns:

1. **Learn the SDK/API first** - Understand before abstracting
2. **Define INPUT_SPEC** - Your contract with the API
3. **Stay in one place until green** - Test success, failure, validation
4. **Run through Gateway** - Gain robustness automatically
5. **Scaffold appears** - DTOs match your spec, not guesses

**See:** Developer Workflow in [concepts/developer-workflow.md](../docs-site/docs/concepts/developer-workflow.md)

---

## Key Principles

1. **Understanding over abstraction** - No magic, explicit contracts
2. **Transport-agnostic** - Same patterns work for SDK, REST, RPC, MCP
3. **Optional repositories** - Use when needed, not enforced
4. **Policy-driven** - Cross-cutting concerns via gateway policies
5. **Result monad** - Explicit error handling, no exceptions
6. **CanonicalDTOs** - Provider-agnostic domain objects
7. **Laravel-first** - Actions, Jobs, Commands, Controllers

---

## What's Next?

### For Traditional API Integration:
1. Read [Pattern Decision Guide](./PATTERN_DECISION_GUIDE.md)
2. Choose CRUD or Operation pattern
3. Review [Non-CRUD Operations](./NON_CRUD_OPERATIONS_PATTERN.md) for details

### For AI Agent Development:
1. Read [MCP Pattern](./MCP_PATTERN.md)
2. Configure MCP servers in `config/mcp.php`
3. Build agent workflows with `CallToolAction`

### For Quick Experiments:
1. Start with Procedure pattern
2. Evolve to Operation/CRUD when ready
3. Migrate guidance in decision guide

---

## Philosophy

> **Plenipotentiary is not a wrapper. It's a stable platform.**

- Your code talks to **your** Gateway, not vendor APIs directly
- Vendor APIs change → Adapter changes, Gateway stays stable
- Storage changes → Repository changes, Gateway stays stable
- Cross-cutting concerns → Policies handle it, Gateway stays stable

**Result:** Your application logic is decoupled from vendor volatility.

---

## Additional Resources

- **Concepts Docs:** `/docs-site/docs/concepts/`
  - Authentication strategies
  - Repository pattern
  - Workflows
  - Testing

- **Example Implementations:**
  - Google Ads (CRUD + SDK)
  - eBay Browse (Operation + REST)
  - OpenAI (Operation + REST)
  - MCP Agents (Operation + MCP)

- **Laravel Integration:**
  - Service Providers
  - Actions (Lorisleiva)
  - Jobs & Commands
  - Controllers
