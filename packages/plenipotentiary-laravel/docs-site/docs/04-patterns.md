---
sidebar_position: 4
title: Patterns
---

# Patterns

Five proven patterns for different integration styles. Pick the pattern that matches your use case, not a one-size-fits-all wrapper. You can use multiple patterns with the same API. These patterns help you handle **heterogeneous integrations** (SDKs, REST, SOAP) with a consistent interface.

## CRUD Pattern

**Resource Lifecycle Management**

Use when: Managing resources with Create/Read/Update/Delete lifecycle

### Structure

```
Pleni/{Provider}/{Domain}/Contexts/{Context}/{Resource}/
  ├── DTO/
  │   └── {Resource}CanonicalDTO.php  (includes fromArray() factory method)
  ├── Selector/
  │   └── {Resource}Selector.php
  ├── Gateway/
  │   └── {Resource}CrudGateway.php
  └── Adapter/
      ├── {Resource}CrudAdapter.php
      ├── {Resource}Create.php
      ├── {Resource}Read.php
      ├── {Resource}ReadMany.php
      ├── {Resource}Update.php
      └── {Resource}Delete.php
```

### Developer Usage

```php
$campaign = CampaignCanonicalDTO::fromArray([
  'name' => 'Summer Sale',
  'budget' => 50000,
  'status' => 'ENABLED',
]);

$result = $gateway->create($campaign);
```

### Feature Coverage

- **Type Safety:** High
- **Validation:** High
- **Discoverability:** High
- **Ease of Setup:** Medium
- **Structure Overhead:** Medium
- **IDE Support:** High

### Real-World Examples

- Google Ads Campaigns
- Stripe Customers
- Shopify Products
- Xero Invoices

## Operation Pattern

**Use Case Driven**

Use when: Operations beyond CRUD that don't act on resource fields - search, generate, verify, calculate. If pausing a campaign (updating status field), use CRUD + Laravel Actions instead to avoid Gateway-calling-Gateway issues.

### Structure

```
Pleni/{Provider}/{Domain}/
  ├── Contexts/Default/Operations/
  │   ├── {UseCase}/
  │   │   ├── {UseCase}Operation.php
  │   │   └── {UseCase}DTO.php
  │   └── Actions/
  │       └── {UseCase}Action.php
  │
  └── Shared/Transfer/
      ├── {Provider}{Domain}OperationGateway.php
      └── {Provider}{Domain}OperationAdapter.php
```

### Developer Usage

```php
// Like CRUD but for non-CRUD use cases
$dto = SearchItemsDTO::fromArray([
  'query' => 'laptop',
  'priceMax' => 500,
  'condition' => 'NEW',
]);

$result = $gateway->searchItems($dto);
```

### Feature Coverage

- **Type Safety:** High
- **Validation:** High
- **Discoverability:** High
- **Ease of Setup:** High
- **Structure Overhead:** High
- **IDE Support:** High

### Real-World Examples

- eBay Browse Search
- OpenAI Completions
- Google Ads Reporting
- Price Calculators

## REST Pattern

**Saloon Request/Response**

Use when: Clean RESTful APIs using Saloon's native Request/Response pattern. Two modes: (1) Operation-like use cases use \{UseCase\}DTO with Gateway for validation/policies, (2) Simple calls use pure Saloon without Gateway overhead. For CRUD operations, use the CRUD pattern instead.

### Structure

```
Pleni/{Provider}/{Domain}/
  ├── Shared/Transfer/Rest/
  │   └── {Provider}{Domain}RestConnector.php
  │
  └── Contexts/Default/
      └── Requests/
          ├── CreateCompletionRequest.php
          ├── GetWeatherRequest.php
          └── SendEmailRequest.php

  Optional (if using Gateway for validation/policies):
  ├── Shared/Transfer/Rest/
  │   ├── {Provider}{Domain}RestGateway.php
  │   └── {Provider}{Domain}RestAdapter.php
  └── DTO/
      └── CreateCompletionDTO.php
```

### Developer Usage

```php
// Mode 1: Pure Saloon (no Gateway) - simple calls
$connector = new WeatherConnector($apiKey);
$response = $connector->send(new GetWeatherRequest(
    location: 'London'
));

// Mode 2: With Gateway + DTO - when you need validation/policies
$result = $gateway->createCompletion(CreateCompletionDTO::fromArray([
    'model' => 'gpt-4',
    'messages' => [...]
]));
```

### Feature Coverage

- **Type Safety:** High
- **Validation:** Medium
- **Discoverability:** High
- **Ease of Setup:** High
- **Structure Overhead:** Low
- **IDE Support:** High

### Real-World Examples

- OpenAI Completions
- Weather APIs
- SendGrid Emails
- GitHub API

## Procedure Pattern

**Simple RPC**

Use when: Quick prototypes, simple one-off operations

### Structure

```
Pleni/{Provider}/{Domain}/
  ├── Contexts/Default/Procedures/
  │   ├── SearchItems.php
  │   ├── SendNotification.php
  │   └── ProcessRefund.php
  │
  └── Shared/Procedure/
      ├── {Provider}{Domain}ProcedureAdapter.php
      ├── {Provider}{Domain}ProcedureGateway.php
      └── {Provider}{Domain}ProcedureConnector.php
```

### Developer Usage

```php
$result = $gateway->call('searchItems', [
  'q' => 'laptop',
  'limit' => 50,
  'filter' => 'price:[..500]',
]);
```

### Feature Coverage

- **Type Safety:** Low
- **Validation:** Low
- **Discoverability:** Low
- **Ease of Setup:** High
- **Structure Overhead:** Low
- **IDE Support:** Low

### Real-World Examples

- Admin Tools
- Quick Scripts
- Rapid Prototyping
- One-off Operations

## MCP Proxy Pattern (Niche)

**Controlled AI Agent Tool Access**

Use when: AI agents (Claude, ChatGPT) need access to high-stakes tools (database queries, email sending, billing operations) and you need **budget tracking, rate limiting, and complete audit trails**

### Structure

```
Pleni/MCP/Database/  (Proxy to Database MCP)
  ├── Gateway/
  │   └── DatabaseMcpProxyGateway.php
  ├── Adapter/
  │   └── DatabaseMcpAdapter.php  (Calls real MCP server)
  ├── Policies/
  │   ├── BudgetPolicy.php
  │   └── RateLimitPolicy.php
  ├── Support/
  │   └── AuditLogger.php
  └── Http/Controllers/
      └── DatabaseMcpController.php  (API endpoints)
```

### Developer Usage

```php
// Claude Desktop calls YOUR Laravel API
// POST /api/mcp/database/query_customers

public function handle(Request $request)
{
    // Your Gateway applies cross-cutting concerns
    $result = $this->gateway->proxyTool(
        toolName: $request->input('tool'),
        params: $request->input('params')
    );

    // Budget tracked, rate limited, fully audited
    // Then proxies to real Database MCP server
    return response()->json($result);
}
```

### Feature Coverage

- **Type Safety:** High
- **Validation:** High
- **Discoverability:** High
- **Ease of Setup:** Low
- **Structure Overhead:** High
- **IDE Support:** High

### Real-World Examples

- Database Queries (High Cost)
- Email Sending (Rate Limited)
- Billing Operations (Audit Required)
- Customer Data Access (GDPR)

## MCP Proxy Pattern: Understanding the Flow

**This is a niche pattern** for when AI agents (Claude, ChatGPT) need access to high-stakes tools and you need budget tracking, rate limiting, and complete audit trails. Your Laravel app acts as a **controlled proxy** between the AI agent and existing MCP servers.

### Calling Claude API (Operation Pattern)
Your app → Claude API

```php
// You call Claude for completions
$response = $claudeGateway->create(
  CreateCompletionDTO::fromArray([
    'model' => 'claude-3-5-sonnet',
    'messages' => [...]
  ])
);
```

### Claude Calls YOUR API (MCP Proxy Pattern)
Claude → Your Laravel API → MCP Server

```php
// Claude calls: POST /api/mcp/database/query
// Your endpoint with safety controls
$result = $gateway->proxyTool(
  tool: 'get_orders',
  params: $request->all()
);

// Budget, rate limit, audit applied
// Then proxies to real Database MCP
```

### Why Proxy MCP Through Your Laravel App?

**❌ AI Calling MCP Directly:**
- No budget tracking across tools
- No rate limiting per agent/session
- No audit trail of AI actions
- Can't enforce business rules
- Runaway costs possible

**✅ With MCP Proxy:**
- Budget limits (max $50/day tracked)
- Rate limiting (100 calls/min enforced)
- Complete audit log of every tool call
- Business rules applied (GDPR, permissions)
- Graceful degradation on overload

### Real-World Example: Customer Support Agent

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

### Real-World Example: AI Debugging with App Telemetry

**Scenario:** AI agent helping you debug production issues by combining MCP database access with your Laravel app's telemetry.

```php
// DatabaseMcpProxyGateway.php
public function proxyTool(string $toolName, array $params): Result
{
    // Execute original MCP tool
    $result = $this->mcpAdapter->executeTool($toolName, $params);

    // AUGMENT with your app telemetry
    if ($toolName === 'query_orders') {
        $telemetry = $this->telemetryService->getRecentMetrics([
            'slow_queries_last_hour' => true,
            'error_rates' => true,
            'active_sessions' => true,
        ]);

        // Inject telemetry into response
        $enrichedResult = array_merge($result, [
            'app_context' => [
                'slow_queries' => $telemetry['slow_queries'],
                'error_rate' => $telemetry['error_rate'],
                'load' => $telemetry['active_sessions'],
                'deployment_version' => config('app.version'),
                'last_deployment' => cache('last_deployment_time'),
            ]
        ]);

        return Result::ok($enrichedResult);
    }

    return Result::ok($result);
}
```

**What the AI receives:**

```json
{
  "orders": [...],
  "app_context": {
    "slow_queries": 15,
    "error_rate": "0.02%",
    "load": 234,
    "deployment_version": "v2.3.1",
    "last_deployment": "2024-01-15 10:30:00"
  }
}
```

Now when you ask: **"Why are orders slow today?"**

The AI can correlate the data with your app telemetry and respond: *"The slow queries increased from 3 to 15 after the v2.3.1 deployment at 10:30 AM. The error rate also doubled from 0.01% to 0.02%."*

**Other telemetry injection possibilities:**
- Cache hit/miss rates
- Queue depths and lag
- API rate limit remaining
- Feature flag states
- A/B test variant assignments
- Recent error logs and stack traces
- Database connection pool stats
- Memory usage and load averages

### When You Need MCP Proxy

**Use MCP Proxy When:**
- AI agents need access to high-stakes tools (database, billing, customer data)
- You need strict budget limits to prevent runaway costs
- Compliance requires complete audit trails (GDPR, SOC2)
- Rate limiting prevents system overload or provider blocking
- You want to augment MCP responses with your app's telemetry/context

**Skip MCP Proxy When:**
- Tools are read-only and low-risk (documentation, logs)
- Claude API's built-in token tracking is sufficient
- You're comfortable with AI calling MCP servers directly
- Simple logging at the conversation level is enough
- No need to inject additional app context

**Key Distinction:** You're **not building MCP servers** (those already exist: @modelcontextprotocol/server-filesystem, server-slack, etc.). You're **proxying them through Laravel HTTP endpoints** to add budget tracking, rate limiting, and audit logging for high-stakes AI agent workflows. This is a niche pattern - most use cases can call Claude/ChatGPT APIs directly (Operation/REST patterns).
