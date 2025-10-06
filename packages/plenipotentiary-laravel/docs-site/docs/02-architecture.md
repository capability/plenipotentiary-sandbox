---
sidebar_position: 2
title: Architecture
---

# Architecture

Promotes understanding vs over abstraction - Four patterns, one stable platform

## Core Principle

**Abstract the Abstractable (CRUD):** Pleni supports CRUD where it fits, but most real integrations need additional patterns. It offers multiple integration patterns to match different API shapes, each built on Laravel's native tooling and proven libraries like Saloon for HTTP.

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

Every pattern returns `Result<CanonicalDTO>` - consistent, predictable, testable. From simplest to most complex syntax:

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

`unwrap()` returns your **canonical DTO** (consistent across all providers).

`rawResponse()` returns the **actual provider response** (Google's MutateCampaignsResponse, Stripe's Charge object, etc.).

**Use it for:** Debugging, logging provider-specific metadata, accessing fields not in your canonical DTO.

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

## Four Gateway/Adapter Patterns

Different abstraction levels for different integration types. REST pattern uses Saloon natively without the Gateway layer.

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

### 2. Operation Pattern - RESTful Use Cases

Action/query operations organized by business use case. Built for REST APIs via Saloon, but not limited to REST. API results often aren't relational - swap to Redis, Mongo, S3, Elasticsearch, or any data store.

**Transport:** REST (Saloon)

**Examples:** eBay Search, OpenAI Completions, Stripe Charges, Custom APIs

**Adapter Files:**
- Adapter/SearchItems/SearchItemsOperation.php
- Adapter/CreateCompletion/CreateCompletionOperation.php
- Adapter/VerifyAvailability/VerifyOperation.php

**Gateway Methods:**
- `search($dto)`
- `createCompletion($dto)`
- `verify($dto)`

**Repository:** Optional/swappable
**Return Type:** `Result<UseCaseResult>`
**Highlight:** Leverages Saloon - best-in-class HTTP client

### 3. Procedure Pattern - Rapid Prototyping

Quick prototyping with dynamic operation names for fast iteration and exploration.

**Transport:** SDK or REST (Saloon)

**Examples:** Admin Tools, Quick Scripts, Prototyping, One-off Tasks

**Adapter Files:**
- Adapter/ProcedureAdapter.php (handles all)

**Gateway Methods:**
- `call($operation, $payload)`

**Repository:** Optional/swappable
**Return Type:** `Result<mixed>`

### 4. MCP Pattern - AI Agent Tool Access

Laravel client for consuming MCP servers - provide filesystem, database, or custom tools to AI agents (like Claude) with budget tracking, rate limiting, and complete audit trails.

**Transport:** MCP (stdio/SSE)

**Examples:** Filesystem Tools, Database Queries, Code Analysis, Multi-step Agents

**Adapter Files:**
- CallTool/CallToolOperation.php
- ReadResource/ReadResourceOperation.php
- ListTools/ListToolsOperation.php

**Gateway Methods:**
- `callTool($dto)`
- `readResource($dto)`
- `listTools()`

**Repository:** Optional/swappable
**Return Type:** `Result<ToolResult>`
**Highlight:** Budget tracking, rate limits, audit logs

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

All adapters define `INPUT_SPEC` as their contract. When teams share adapters, INPUT_SPEC becomes an invaluable kickstart - everyone knows exactly what fields are needed, validation rules, and defaults.

```php
public const INPUT_SPEC = [
    'query' => ['rules' => ['required', 'string', 'min:2']],
    'limit' => ['rules' => ['integer', 'max:200'], 'default' => 50],
    'priceMax' => ['rules' => ['numeric']],
];

// Gateway validates automatically via INPUT_SPEC
// Teams immediately understand the contract
```

### Understanding the MCP Pattern: AI Agents with Safe Tool Access

The MCP pattern is fundamentally different from the other four patterns. You're not integrating with a traditional API—you're building **infrastructure to give AI agents safe, controlled access to tools** via Anthropic's Model Context Protocol.

#### The Complete Flow

1. **User asks:** "Find all inactive customers and send re-engagement emails"
2. **Your Laravel app** sends this to Claude API with available MCP tools listed
3. **Claude thinks:** "I need to query the database" and requests `query_database` tool
4. **Your MCP Gateway executes** the query via MCP server (budget/rate limit policies run here)
5. **Results go back to Claude** who analyzes: "Found 52 inactive customers"
6. **Claude requests** 52 `send_email` tool calls
7. **Your MCP Gateway executes** each email via Mailchimp MCP server (budget tracking: $0.53 spent, $49.47 remaining)
8. **Claude reports:** "Sent 52 re-engagement emails to inactive customers"

#### Why Safety Features Are Critical

AI agents can make dozens or hundreds of tool calls autonomously. Without guardrails:
- Claude queries your database 10,000 times → $100 in costs before you notice
- Runaway loop sends 50,000 emails in 10 seconds → provider blocks you
- No audit trail → can't debug what went wrong or replay the session

Your MCP Gateway with **budget policies** (max $50/day), **rate limits** (max 100 calls/minute), and **complete audit logs** prevents all of this.

#### Current State vs. Future Vision

**What's Built Now:**
- MCP Gateway with budget tracking & rate limiting
- Tool execution via MCP servers (filesystem, database, etc.)
- Complete audit trail of every tool call
- Can be used for deterministic PHP workflows or external tool access

**Coming Soon (AI Orchestrator):**
- Anthropic SDK integration (Claude + your MCP tools)
- OpenAI function calling integration (GPT + your MCP tools)
- Multi-turn conversation handling
- Tool registry for defining which agents can use which tools

**Key Distinction:** You're **not building MCP servers** (those already exist: @modelcontextprotocol/server-filesystem, server-slack, etc.). You're **consuming them from Laravel** with the same Gateway/Adapter consistency as your other integrations, but adding AI-agent-specific safety policies that prevent runaway costs and system overload.
