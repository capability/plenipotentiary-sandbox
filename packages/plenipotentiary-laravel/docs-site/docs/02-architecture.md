---
sidebar_position: 2
title: Architecture
---

# Architecture

Promotes understanding vs over-abstraction - Five patterns, one stable platform

## Core Principle

**Abstract the Abstractable (CRUD):** Pleni supports CRUD where it fits but doesn't pretend it covers everything. It offers multiple integration patterns to match different API shapes - each built on Laravel's native tooling and proven libraries like Saloon for HTTP.

## How Plenipotentiary Works: The Flow

```
Your Application → Gateway → Adapter → External API
(You write)      (Provided) (You write) (Third-party)
```

### Your Application Domain
Controllers, Jobs, Commands
*You write this*

### Gateway
Stable, consistent contracts
*✓ Plenipotentiary provides*

### Adapter
API integration logic
*You write this*

### External API
Stripe, Google, etc.
*Third-party service*

## What You Write vs What Plenipotentiary Provides

### What You Write
Your application code and the Adapter (actual API integration logic). This is NOT a magic wrapper; you still implement the integration, but with structure and safety guardrails.

### What Plenipotentiary Provides
The Gateway layer (stable contracts, validation, policies) and scaffolding commands to generate boilerplate.

## Your Application Domain

Use any Laravel pattern you know - all return Result&lt;T&gt;

### Consistent Result Interface

Every pattern returns a consistent `Result<T>` interface - whether `Result<CanonicalDTO>` (CRUD) or `Result<\{UseCase\}DTO>` (Operation). Predictable, testable, transport-agnostic. From simplest to most complex syntax:

#### 1. Simplest: Basic Success Check

```php
$result = $gateway->create($dto);

if ($result->isOk()) {
    // Success! Use the canonical DTO
    $campaign = $result->unwrap();
    echo $campaign->externalId; // '12345'
}
```

#### 2. Error Handling

```php
$result = $gateway->update($dto);

if ($result->isErr()) {
    // Provider error (network, API limit, etc.)
    $error = $result->error();
    Log::error('Update failed', $error);
    return response()->json($error, 500);
}
```

#### 3. Complete: Provider Errors + Raw Response Access

```php
$result = $gateway->create($dto);
// Gateway already validated $dto against INPUT_SPEC before calling adapter
// If INPUT_SPEC failed, we wouldn't reach the adapter at all

// Provider rejected our data (Google's validation, not ours)
if ($result->isInvalid()) {
    // Google Ads rejected the campaign (budget too low, invalid name, etc.)
    $rawResponse = $result->rawResponse(); // Check Google's actual error
    return response()->json([
        'message' => 'Provider rejected data',
        'violations' => $result->violations()
    ], 422);
}

// Provider error (network, API limit, auth failure, etc.)
if ($result->isErr()) {
    return response()->json($result->error(), 500);
}

// Success: Get canonical DTO AND raw provider response
$campaign = $result->unwrap();           // Canonical DTO (consistent)
$rawResponse = $result->rawResponse();   // Provider response (for debugging)

Log::info('Campaign created', [
    'externalId' => $campaign->externalId,
    'resourceName' => $rawResponse->getResults()[0]->getResourceName(),
]);
```

#### What is rawResponse()?

`unwrap()` returns your **domain DTO** (CanonicalDTO for CRUD, \{UseCase\}DTO for Operation - consistent across providers).

`rawResponse()` returns the **actual provider response** (Google's MutateCampaignsResponse, Stripe's Charge object, eBay's SearchResponse, etc.).

**Use it for:** Debugging, logging provider-specific metadata, accessing fields not in your domain DTO.

### Laravel Integration Examples

Every pattern returns `Result<T>` with the same methods: `isOk()`, `isErr()`, `isInvalid()`, `unwrap()`, `rawResponse()`

#### Controller

**Option 1: Direct Gateway Usage**
```php
public function store(Request $req, CampaignGateway $gateway) {
    $dto = CampaignCreateDTO::fromArray($req->validated());
    $result = $gateway->create($dto);

    return $result->isOk()
        ? response()->json($result->unwrap())
        : response()->json($result->error(), 400);
}
```

**Option 2: Via Action (recommended)**
```php
public function store(Request $req, CreateCampaignAction $action) {
    $result = $action->handle($req->validated());

    return $result->isOk()
        ? response()->json($result->unwrap())
        : response()->json($result->error(), 400);
}

// The Action internally uses the Gateway:
// class CreateCampaignAction {
//     public function __construct(private CampaignGateway $gateway) {}
//     public function handle(array $data): Result {
//         $dto = CampaignCreateDTO::fromArray($data);
//         return $this->gateway->create($dto);
//     }
// }
```

#### Job

```php
class SyncCampaignsJob implements ShouldQueue {
    public function handle(CampaignGateway $gateway) {
        $result = $gateway->readMany(['status' => 'ENABLED']);

        if ($result->isErr()) {
            $this->fail($result->error());
            return;
        }

        $campaigns = $result->unwrap();
        // Sync to database...
    }
}
```

#### Command

```php
class CreateCampaignCommand extends Command {
    public function handle(CampaignGateway $gateway) {
        $dto = CampaignCreateDTO::fromArray([
            'name' => $this->argument('name'),
            'budget' => $this->option('budget'),
        ]);

        $result = $gateway->create($dto);
        // Gateway already checked INPUT_SPEC before calling adapter

        // Provider (Google Ads) rejected our data
        if ($result->isInvalid()) {
            $this->error('Google Ads rejected the campaign:');

            // Check raw response for Google's actual error details
            $raw = $result->rawResponse();
            $googleError = $raw->getPartialFailureError();

            foreach ($result->violations() as $v) {
                $this->line("  {$v['field']}: {$v['message']}");
            }
            return 1;
        }

        // Provider error (network, auth, rate limit, etc.)
        if ($result->isErr()) {
            $this->error($result->error()['message']);
            return 1;
        }

        // Success: canonical DTO + raw response
        $campaign = $result->unwrap();
        $this->info("Created: {$campaign->externalId}");

        // Access raw Google Ads response for detailed logging
        $raw = $result->rawResponse();
        $this->comment("Resource: {$raw->getResults()[0]->getResourceName()}");

        return 0;
    }
}
```

#### Action

**Implementation:**
```php
class CreateCampaignAction extends Action {
    public function __construct(
        private CampaignGateway $gateway
    ) {}

    public function handle(array $data): Result {
        $dto = CampaignCreateDTO::fromArray($data);
        return $this->gateway->create($dto);
    }
}
```

**Usage:**
```php
$result = CreateCampaignAction::run(['name' => 'Black Friday']);

// Same Result<T> interface everywhere
if ($result->isOk()) {
    $campaign = $result->unwrap();
    $rawResponse = $result->rawResponse();
}
```

## Five Gateway/Adapter Patterns

Different patterns for different use cases. Pick the pattern that matches your use case, not a one-size-fits-all wrapper. You can use multiple patterns with the same API. These patterns help you handle **heterogeneous integrations** (SDKs, REST, SOAP) with a consistent interface.

### 1. CRUD Pattern - Abstractable Resource Lifecycle

Full lifecycle management with Create/Read/Update/Delete operations on resource-based APIs (campaigns, invoices, customers, products).

**Transport:** SDK or REST (Saloon)

**Examples:** Google Ads Campaigns, Stripe Customers, Shopify Products

**Adapter Files:**
- CampaignCreate.php
- CampaignRead.php
- CampaignReadMany.php
- CampaignUpdate.php
- CampaignDelete.php

**Gateway Methods:**
- `create($dto)`
- `read($id)`
- `readMany($filter)`
- `update($dto)`
- `delete($id)`

**Repository:** Optional
**Return Type:** `Result<CanonicalDTO>`

### 2. Operation Pattern - Use Case Driven

Operations beyond CRUD that don't act on resource fields - search, generate, verify, calculate. If pausing a campaign (updating status field), use CRUD + Laravel Actions instead to avoid Gateway-calling-Gateway issues.

**Transport:** SDK or REST (Saloon)

**Examples:** eBay Browse Search, OpenAI Completions, Google Ads Reporting, Price Calculators

**Adapter Files:**
- DTO/SearchItemsDTO.php
- Adapter/SearchItems/SearchItemsOperation.php
- Adapter/CreateCompletion/CreateCompletionOperation.php

**Gateway Methods:**
- `search($dto)`
- `createCompletion($dto)`

**Repository:** Optional/swappable
**Return Type:** `Result<\{UseCase\}DTO>`
**Highlight:** For operations that don't map to resource field changes

### 3. REST Pattern - Native Saloon

Clean RESTful APIs using Saloon's native Request/Response pattern. Two modes: (1) Operation-like use cases use \{UseCase\}DTO with Gateway for validation/policies, (2) Simple calls use pure Saloon without Gateway overhead. For CRUD operations, use the CRUD pattern instead.

**Transport:** REST (Saloon)

**Examples:** OpenAI Completions, Weather APIs, SendGrid Emails, GitHub API

**Adapter Files:**
- Rest/Connector.php
- Requests/CreateCompletionRequest.php
- Requests/GetWeatherRequest.php
- (Optional) DTO/CreateCompletionDTO.php

**Gateway Methods:**
- Mode 1: `$gateway->execute($completionDTO)`
- Mode 2: `$connector->send(new GetWeatherRequest())`

**Repository:** Flexible
**Return Type:** `Result<\{UseCase\}DTO>` OR `Saloon Response`
**Highlight:** For CRUD operations, use CRUD pattern. REST is for operations and simple calls.

### 4. Procedure Pattern - Rapid Prototyping

Quick prototyping with dynamic operation names for fast iteration and exploration.

**Transport:** SDK or REST (Saloon)

**Examples:** Admin Tools, Quick Scripts, Prototyping, One-off Tasks

**Adapter Files:**
- Adapter/ProcedureAdapter.php (handles all)

**Gateway Methods:**
- `call($operation, $payload)`

**Repository:** Optional/swappable
**Return Type:** `Result<mixed>`

### 5. MCP Proxy Pattern - Controlled AI Agent Tool Access (Niche)

Proxy MCP servers through your Laravel app to add budget tracking, rate limiting, and audit trails when AI agents (Claude, ChatGPT) need controlled access to high-stakes tools (database, email, billing).

**Transport:** HTTP API → MCP (stdio/SSE)

**Examples:** Proxy Database MCP, Proxy Filesystem MCP, Proxy Slack MCP, Proxy Email MCP

**Adapter Files:**
- Adapter/McpProxyAdapter.php
- Support/McpServerConnector.php (stdio/SSE)
- Http/Controllers/McpProxyController.php

**Gateway Methods:**
- `proxyToolCall($tool, $params)`
- `forwardToMcpServer($request)`

**Repository:** N/A (proxies existing MCP servers)
**Return Type:** `Result<McpToolResult>`
**Highlight:** Proxies existing MCP servers, doesn't create new ones

## Gateway Layer: Your Stable Platform

**Gateway = Stable platform.** Your application calls the Gateway, never the vendor API directly. When provider APIs change, only the Adapter changes - your Gateway stays stable. All gateways automatically apply cross-cutting concerns through Laravel's native tools.

### Cross-Cutting Concerns

The Gateway layer provides a consistent location to apply:

1. **Logging** - Laravel Log facade
2. **Retries** - Automatic backoff
3. **Idempotency** - Laravel Cache
4. **Error Mapping** - Domain exceptions
5. **Rate Limiting** - Laravel RateLimiter
6. **Observability** - Metrics & events

All applied via `GatewayPolicy`.

### Team Collaboration via INPUT_SPEC

All adapters define `INPUT_SPEC` as their contract. When sharing adapters, INPUT_SPEC becomes an invaluable kickstart - self documenting errors ensures everyone knows exactly what fields are needed, validation rules, and defaults. This is what YOUR domain needs, not everything the API/SDK call supports (See step 2 in the developer workflow).

```php
// CampaignCreate.php
public const INPUT_SPEC = [
    'name' => [
        'rules' => ['required', 'string', 'min:1', 'max:128'],
    ],
    'status' => [
        'rules' => ['nullable', 'in:ENABLED,PAUSED,REMOVED'],
    ],
    'budgetMicros' => [
        'rules' => ['nullable', 'numeric', 'min:0'],
    ],
    'budgetResourceName' => [
        'rules' => ['nullable', 'string'],
    ],
    // customerId comes from providerContext - auto-injected
    'providerContext.google.customerId' => [
        'rules' => ['required', 'string'],
        'source' => 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID',
    ],
];

// Gateway validates automatically via INPUT_SPEC
// Teams immediately understand the contract
```

### Understanding the MCP Proxy Pattern: Controlled AI Tool Access

**This is a niche pattern** for when AI agents (Claude, ChatGPT) need access to high-stakes tools (database queries, email sending, billing operations) and you need **budget tracking, rate limiting, and complete audit trails**. Your Laravel app acts as a **controlled proxy** between the AI agent and existing MCP servers.

#### The Complete Flow

1. **User asks Claude Desktop:** "Find all inactive customers and send re-engagement emails"
2. **Claude analyzes** the request and decides it needs tools
3. **Claude calls YOUR Laravel API:** `POST /api/mcp/database/query_customers` (configured to call your endpoint, not the MCP server directly)
4. **Your MCP Proxy Gateway** checks budget, applies rate limit, logs request
5. **Gateway forwards** to real MCP server → Database MCP executes query
6. **Results return** to Claude via YOUR API (budget tracked: $0.01 spent)
7. **Claude analyzes:** "Found 52 inactive customers, need to send emails"
8. **Claude calls YOUR endpoint** 52 times: `POST /api/mcp/email/send` (all tracked)
9. **Gateway proxies** to Email MCP server, tracks budget ($0.52 total), enforces rate limits
10. **Claude reports:** "Sent 52 re-engagement emails" (complete audit trail logged)

#### When You Need MCP Proxy

**Use MCP Proxy When:**
- AI agents need access to high-stakes tools (database, billing, customer data)
- You need strict budget limits to prevent runaway costs
- Compliance requires complete audit trails (GDPR, SOC2)
- Rate limiting prevents system overload or provider blocking

**Skip MCP Proxy When:**
- Tools are read-only and low-risk (documentation, logs)
- Claude API's built-in token tracking is sufficient
- You're comfortable with AI calling MCP servers directly
- Simple logging at the conversation level is enough

**Key Distinction:** You're **not building MCP servers** (those already exist: @modelcontextprotocol/server-filesystem, server-slack, etc.). You're **proxying them through Laravel HTTP endpoints** to add budget tracking, rate limiting, and audit logging for high-stakes AI agent workflows. This is a niche pattern - most use cases can call Claude/ChatGPT APIs directly (Operation/REST patterns).
