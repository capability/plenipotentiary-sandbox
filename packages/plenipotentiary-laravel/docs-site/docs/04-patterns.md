---
sidebar_position: 4
title: Patterns
---

# Patterns

Five proven patterns for different integration styles. Pick the one that matches your API, not a one-size-fits-all wrapper.

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

Use when: Non-CRUD operations on SDKs - search, generate, verify, calculate

### Structure

```
Pleni/{Provider}/{Domain}/
  ├── Contexts/Default/Operations/
  │   ├── {UseCase}/
  │   │   ├── {UseCase}Operation.php
  │   │   ├── {UseCase}DTO.php
  │   │   └── {UseCase}Result.php
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

## MCP Pattern

**AI Agents Calling YOUR Tools**

Use when: Your AI agent needs controlled access to tools in YOUR system (filesystem, database, APIs)

### Structure

```
Pleni/MCP/
  ├── Contexts/Default/
  │   └── Operations/CallTool/
  │       ├── CallToolOperation.php
  │       ├── CallToolGateway.php
  │       └── CallToolDTO.php
  ├── Shared/
  │   ├── Transport/McpClient.php
  │   ├── Support/McpServerRegistry.php
  │   └── Policies/
  │       ├── AgentBudgetPolicy.php
  │       └── AgentRateLimitPolicy.php
```

### Developer Usage

```php
// AI agent calls YOUR tool (reverse direction!)
$result = app(CallToolAction::class)->handle(
    server: 'customer-database',
    tool: 'get_customer_orders',
    arguments: ['customer_id' => 12345],
    agentId: 'support-agent-claude'
);

// Budget tracked, rate limited, fully audited
// AI accesses YOUR resources safely
```

### Feature Coverage

- **Type Safety:** 100%
- **Validation:** 100%
- **Discoverability:** 100%
- **Ease of Setup:** 70%
- **Persistence:** 100%
- **Idempotency:** 100%

### Real-World Examples

- AI Reading Customer Data
- AI Querying Databases
- AI Accessing Filesystem
- AI Calling Internal APIs

## MCP Pattern vs. AI Integration Pattern

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

### Claude Calls YOUR Tools (MCP Pattern)
Claude → Your system's tools

```php
// Claude calls YOUR database
$result = $mcpGateway->callTool(
  CallToolDTO::fromArray([
    'server' => 'customer-db',
    'tool' => 'get_orders',
    'agentId' => 'claude-support'
  ])
);
```

### Why MCP Pattern Matters

**❌ Without MCP:**
- Fetch ALL customer data upfront
- Dump everything in prompt
- Hope it fits in context window
- No budget tracking
- No audit trail

**✅ With MCP:**
- AI asks for specific data when needed
- Budget limits (max 100 queries/agent)
- Rate limiting (10 queries/minute)
- Full audit trail of AI access
- Policies can require human approval

### Real-World Example: Customer Support Agent

You run Claude in your Laravel app. When a user asks about their order, Claude needs to check your database:

1. **User asks:** "What did I order last month?"
2. **You send to Claude** (Operation Pattern - your app → Claude API)
3. **Claude responds:** "I need to call get_customer_orders"
4. **You execute tool via MCP** (MCP Pattern - Claude's request → YOUR tool)
5. **MCP enforces policies:** Budget check, rate limit, permissions
6. **You send result back to Claude** with the order data
7. **Claude responds to user** with the answer
