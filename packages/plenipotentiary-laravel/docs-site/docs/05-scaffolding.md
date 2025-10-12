---
sidebar_position: 5
title: Scaffolding
---

# Scaffolding

Generate the exact artisan command to scaffold your integration structure

## What Scaffolding Generates

The scaffold creates your **folder structure**, **stub files**, and **base classes**. You'll then follow the Developer Workflow to:

✓ **Write your adapter files** – Implement the actual HTTP requests using Saloon (or other transport)

✓ **Write your integration logic** – Map API responses to your DTOs, handle errors, add business logic

✓ **Configure cross-cutting concerns** – Add policies, queuing, caching, logging as needed

## A Note About the Repository Pattern

Pleni offers a Repository layer but there is no mandate. If your use case is simple and you're happy with direct Eloquent usage, you do not need to adopt the Repository pattern.

But bear in mind: **not all API results are relational**. This is one of the rare cases when you might actually want to use different persistence layers for different types of analysis:

- **Time-series data:** API analytics or metrics are better stored in InfluxDB or TimescaleDB for efficient time-based queries
- **Search results:** Product catalogs or eBay listings benefit from Elasticsearch for full-text search and faceted filtering
- **Document-based data:** OpenAI completions or unstructured API responses fit better in MongoDB or DynamoDB
- **Session/cache data:** Temporary API results or rate limit tracking work well in Redis for fast access and automatic expiration
- **File-based storage:** Large responses or blob data (images, PDFs) may be better stored in S3 with metadata in your database

The Repository pattern gives you the flexibility to swap storage layers without changing your application code—exactly what you need when different data types require different analysis tools.

## Patterns Available

### CRUD Pattern
**Resource lifecycle management**

```bash
php artisan pleni:make:crud \
  --provider={Provider} \
  --domain={Domain} \
  --resource={Resource}
```

### Operation Pattern
**Use case driven operations (search, calculate, verify)**

```bash
php artisan pleni:make:operation \
  --provider={Provider} \
  --domain={Domain} \
  --resource={Resource}
```

### Procedure Pattern
**Simple RPC-style quick operations or prototyping**

```bash
php artisan pleni:make:procedure \
  --provider={Provider} \
  --domain={Domain} \
  --resource={Resource}
```

### REST Pattern
**Saloon Request/Response for clean RESTful APIs**

```bash
php artisan pleni:make:rest \
  --provider={Provider} \
  --domain={Domain} \
  --resource={Resource}
```

### MCP Proxy Pattern (Niche)
**Proxy MCP servers through Laravel for controlled AI tool access with budget/rate limits**

```bash
php artisan pleni:make:mcp-proxy \
  --server={ServerName} \
  --with-budget \
  --with-rate-limit \
  --with-audit
```

## Optional Components

Add these flags to scaffold additional Laravel components:

### Laravel Actions
`--with-actions`
Generate Action classes for business logic
+3 files

### Repository Layer
`--with-repository`
Add persistence repositories (Eloquent, Mongo, etc.)
+2 files

### Artisan Commands
`--with-commands`
Create CLI commands for operations
+2 files

### Queue Jobs
`--with-jobs`
Add queueable job classes
+3 files

### HTTP Controllers
`--with-controller`
Generate API controllers
+1 file

### Form Requests
`--with-requests`
Add validation request classes
+2 files

### Test Suite
`--with-tests`
Generate unit, feature, and integration tests
+5 files

### Database Migrations
`--with-migrations`
Create database migration files
+1 file

### Model Factories
`--with-factories`
Add factory classes for testing
+1 file

### Database Seeders
`--with-seeders`
Generate seeder classes
+1 file

### Policy Chain
`--with-policies`
Generate budget, rate limit, and permission policies
+3 files

### MCP Proxy Adapter
`--with-mcp-proxy`
Generate adapter to proxy requests to existing MCP servers (stdio/SSE)
+2 files

### Audit Trail
`--with-audit`
Generate audit logging for AI tool access tracking
+2 files

## Real-World Examples

### Google Ads Campaign Sync

Google Ads SPAG Campaign sync - pauses/resumes ad groups based on stock availability. CRUD Pattern with Actions, Commands and Queue Jobs, Test Suite.

**Use Case:** Assumes local domain already has a database representation of the campaign structure

```bash
php artisan pleni:make:crud \
  --provider=Google \
  --domain=Ads \
  --resource=Campaign \
  --with-actions \
  --with-commands \
  --with-jobs \
  --with-tests
```

**Cross-Cutting Concerns:**
- **Rate Limiting** (Global) - Google Ads API has strict rate limits (10K requests/day). Global policy prevents exceeding limits.
- **Logging** (Global) - Track all campaign pause/resume operations for compliance audit trail.
- **Error Mapping** (Provider) - Maps Google Ads SDK exceptions to domain errors (e.g., CAMPAIGN_NOT_FOUND, BUDGET_EXHAUSTED).
- **Idempotency** (Context) - Prevent duplicate campaign updates when job is retried. Uses campaign ID + operation hash.

### eBay Browse API

Backend API that drives a custom JS frontend. Operation Pattern with HTTP Controllers and test suite.

**Use Case:** RESTful API endpoints for frontend consumption

```bash
php artisan pleni:make:operation \
  --provider=eBay \
  --domain=Browse \
  --resource=SearchItems \
  --with-controller \
  --with-tests
```

**Cross-Cutting Concerns:**
- **Retry with Backoff** (Global) - eBay API can be flaky. Automatically retry failed searches with exponential backoff (3 attempts).
- **Logging** (Global) - Log search queries and response times for performance monitoring.
- **Error Mapping** (Provider) - Normalizes eBay API errors (INVALID_QUERY, CATEGORY_NOT_FOUND) to domain exceptions.

### Internal Admin Alerts

Quick internal tool for sending system alerts to Slack/Teams. Procedure Pattern with Artisan command for one-off admin tasks.

**Use Case:** Rapid prototyping for internal tools without full CRUD scaffolding

```bash
php artisan pleni:make:procedure \
  --provider=InternalAPI \
  --domain=Admin \
  --resource=SendAlert \
  --with-commands
```

**Cross-Cutting Concerns:**
- **Logging** (Global) - Track all alert sends for audit trail (who sent what alert when).
- **Error Mapping** (Provider) - Maps internal API errors (INVALID_CHANNEL, RATE_LIMITED) to domain exceptions.

### GitHub Repository Integration

GitHub API integration with 50+ endpoints. REST Pattern uses Saloon's Request/Connector pattern with dedicated request classes for each endpoint (SearchRepos, GetRepo, CreateRepo, UpdateRepo, etc.). Clean, type-safe API calls.

**Use Case:** When you need Saloon's familiar Request pattern with optional Gateway features (policies, validation, persistence)

```bash
php artisan pleni:make:rest \
  --provider=GitHub \
  --domain=API \
  --resource=Repositories \
  --with-requests \
  --with-tests
```

**Cross-Cutting Concerns:**
- **Rate Limiting** (Global) - GitHub API rate limits: 5,000/hour authenticated, 60/hour unauthenticated. Global policy tracks limits.
- **Retry with Backoff** (Global) - Retry failed requests on 502/503/504 errors with exponential backoff.
- **Logging** (Global) - Log all API calls for debugging and monitoring rate limit consumption.
- **Error Mapping** (Provider) - Maps GitHub API errors (NOT_FOUND, FORBIDDEN, VALIDATION_FAILED) to domain exceptions.

### AI Log Analyzer Tool Proxy

Proxy the Filesystem MCP server through your Laravel API. Claude/GPT calls YOUR endpoint, which forwards to the real Filesystem MCP server with budget tracking, rate limits, and audit logs to prevent runaway costs from AI agents reading logs.

**Use Case:** When AI agents need controlled access to existing Filesystem MCP tools with strict budget limits, rate control, and complete audit trails

```bash
php artisan pleni:make:mcp-proxy \
  --server=FilesystemMCP \
  --with-actions \
  --with-policies \
  --with-mcp-proxy \
  --with-audit \
  --with-tests
```

**Cross-Cutting Concerns:**
- **MCP Proxy Budget Policy** (Pattern) - Prevent runaway AI costs when proxying MCP tool calls. Track and limit total calls per day/hour.
- **MCP Proxy Rate Limit** (Pattern) - Prevent AI agent abuse when proxying MCP servers. Max 100 tool calls per minute, 1000 per hour.
- **Logging** (Global) - Log every MCP tool call proxied through your Laravel API: which tool, which agent, when, and what data was accessed.
- **MCP Proxy Audit Policy** (Pattern) - Full audit trail of all MCP tool calls proxied through Laravel for compliance, GDPR, and security review.

### Customer Database MCP Proxy

Proxy a custom Database MCP server through your Laravel API. Claude/GPT calls YOUR endpoint (POST /api/mcp/database/query), which forwards to the real Database MCP server with budget enforcement, query limits, and complete audit logs of all customer data access.

**Use Case:** When AI agents need controlled access to high-stakes customer data via MCP tools with strict budget limits, rate control, and GDPR-compliant audit trails

```bash
php artisan pleni:make:mcp-proxy \
  --server=DatabaseMCP \
  --with-actions \
  --with-jobs \
  --with-policies \
  --with-mcp-proxy \
  --with-audit \
  --with-tests
```

**Cross-Cutting Concerns:**
- **MCP Proxy Budget Policy** (Pattern) - Complex multi-step AI workflows can make hundreds of MCP tool calls. Enforce strict budget limits on proxied requests.
- **MCP Proxy Rate Limit** (Pattern) - Multi-server AI workflows require multiple tool calls. Prevent abuse by limiting proxied MCP requests per minute/hour.
- **Logging** (Global) - Log all database queries proxied through your MCP Gateway: which customer data was accessed, when, and by which AI agent.
- **MCP Proxy Audit Policy** (Pattern) - Track all customer data access by AI agents via MCP proxy for GDPR compliance and security audits.
- **Idempotency** (Context) - Prevent duplicate MCP tool calls when AI workflow is retried. Cache proxied results by customer ID + query hash.

## What is a Context?

A context is a way of grouping resources that "work differently depending on how you use them." For example, Google Ads has multiple campaign types (Search, Display, Shopping, Performance Max) - they're all "campaigns," but each has different rules and setup.

You can model your "Search" strategy as Campaigns, Ad Groups, and Ads in one context called "Search." Create a new context for Shopping campaigns with different rules and DTOs. They all share the same stable Gateway contract, keeping your app code clean.

**Contexts are optional** - use them when you need logical separation. For folder consistency, if no context is specified, a "Default" context will be used.

## Cross-Cutting Concern Scope Levels

### Global
Applied to all providers across your entire application.
Examples: Logging, Rate Limiting, Retry with Backoff

### Provider
Applied to one provider across all contexts.
Examples: Error Mapping (Google Ads specific errors, eBay specific errors)

### Context
Applied to a specific resource or context.
Examples: Idempotency hints specific to Campaign operations

### Pattern
Pattern-specific concerns (e.g., MCP Proxy-only).
Examples: MCP Proxy Budget Policy, MCP Proxy Rate Limit Policy, MCP Proxy Audit Policy
