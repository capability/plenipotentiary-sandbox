---
sidebar_position: 4
title: Patterns
---

# Patterns

Five proven patterns for different integration styles. Pick the one that matches your API, not a one-size-fits-all wrapper. These patterns help you handle **heterogeneous integrations** (SDKs, REST, SOAP) with a consistent interface.

## CRUD Pattern

**Resource Lifecycle Management**

Use when: Managing resources with Create/Read/Update/Delete lifecycle

### Structure

```
Pleni/{Provider}/{Domain}/Contexts/{Context}/{Resource}/
  ├── DTO/
  │   └── {Resource}CanonicalDTO.php
  ├── Factory/
  │   └── {Resource}CanonicalFactory.php
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

- **Type Safety:** 100%
- **Validation:** 100%
- **Discoverability:** 100%
- **Ease of Setup:** 60%
- **Persistence:** 100%
- **Idempotency:** 100%

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

- **Type Safety:** 100%
- **Validation:** 100%
- **Discoverability:** 100%
- **Ease of Setup:** 80%
- **Persistence:** 80%
- **Idempotency:** 100%

### Real-World Examples

- eBay Browse Search
- OpenAI Completions
- Google Ads Reporting
- Price Calculators

## REST Pattern

**Saloon Request/Response**

Use when: Clean RESTful APIs where Saloon's native pattern is perfect

### Structure

```
Pleni/{Provider}/{Domain}/
  ├── Shared/Transfer/Rest/
  │   └── {Provider}{Domain}RestConnector.php
  │
  └── Contexts/Default/{Resource}/
      └── Requests/
          ├── CreatePaymentRequest.php
          ├── GetCustomerRequest.php
          └── ProcessRefundRequest.php

  Optional (if using Gateway pattern):
  ├── Shared/Transfer/Rest/
  │   ├── {Provider}{Domain}RestGateway.php
  │   └── {Provider}{Domain}RestAdapter.php
```

### Developer Usage

```php
// Pure Saloon - use if you don't need Gateway features
$stripe = new StripeConnector($apiKey);
$response = $stripe->send(new CreatePaymentRequest(
    amount: 5000,
    currency: 'usd'
));

// With Gateway - use when you need validation/policies
$result = $gateway->createPayment(CreatePaymentDTO::fromArray([
    'amount' => 5000,
    'currency' => 'usd'
]));
```

### Feature Coverage

- **Type Safety:** 90%
- **Validation:** 60%
- **Discoverability:** 90%
- **Ease of Setup:** 95%
- **Persistence:** 20%
- **Idempotency:** 60%

### Real-World Examples

- Stripe Payments
- SendGrid Emails
- Twilio SMS
- Most RESTful APIs

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

- **Type Safety:** 40%
- **Validation:** 40%
- **Discoverability:** 40%
- **Ease of Setup:** 100%
- **Persistence:** 40%
- **Idempotency:** 40%

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

- **Type Safety:** 100%
- **Validation:** 100%
- **Discoverability:** 100%
- **Ease of Setup:** 70%
- **Persistence:** 100%
- **Idempotency:** 100%

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

### When You Need MCP Proxy

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
