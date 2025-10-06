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

### MCP Pattern
**AI agents calling YOUR tools (database, filesystem, APIs)**

```bash
php artisan pleni:make:mcp-tool \
  --server={ServerName} \
  --tool={ToolName}
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

### MCP Server Definition
`--with-mcp-server`
Generate MCP server config and tool schemas
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

### AI Log Analyzer Tool

Expose a tool that lets AI agents read application logs from your filesystem. The agent analyzes errors with Claude/GPT and suggests fixes. MCP Pattern with budget/rate limit controls to prevent runaway costs.

**Use Case:** Give AI agents controlled access to your filesystem with safety guardrails, budget limits, and full audit trails

```bash
php artisan pleni:make:mcp-tool \
  --server=Filesystem \
  --tool=ReadLogFile \
  --with-actions \
  --with-policies \
  --with-mcp-server \
  --with-audit \
  --with-tests
```

**Cross-Cutting Concerns:**
- **Agent Budget Policy** (Pattern) - Prevent runaway AI costs. Limit max tokens per agent invocation and per day.
- **Agent Rate Limit** (Pattern) - Prevent agent abuse. Max 100 tool calls per minute, 1000 per hour.
- **Logging** (Global) - Log which files the AI accessed, when, and what it read for debugging and compliance.
- **Audit Policy** (Pattern) - Full audit trail of all filesystem access by AI agents for compliance and security review.

### Customer Database Query Tool

Expose a tool that lets AI agents query your customer database (orders, preferences, purchase history). The agent analyzes patterns, checks inventory, and generates personalized recommendations. MCP Pattern enforces query limits and logs all data access.

**Use Case:** Give AI agents safe, audited access to sensitive customer data with strict budget and rate limits

```bash
php artisan pleni:make:mcp-tool \
  --server=CustomerDatabase \
  --tool=QueryCustomerData \
  --with-actions \
  --with-jobs \
  --with-policies \
  --with-mcp-server \
  --with-audit \
  --with-tests
```

**Cross-Cutting Concerns:**
- **Agent Budget Policy** (Pattern) - Complex multi-step workflow can consume significant tokens. Enforce strict budget limits.
- **Agent Rate Limit** (Pattern) - Multi-server workflow requires multiple tool calls. Prevent abuse with rate limits.
- **Logging** (Global) - Log all database queries executed by AI: which customer data was accessed, when, and by which agent.
- **Audit Policy** (Pattern) - Track all customer data access by AI agents for GDPR compliance and security audits.
- **Idempotency** (Context) - Prevent duplicate queries when agent workflow is retried. Cache results by customer ID + query hash.

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
Pattern-specific concerns (e.g., MCP-only).
Examples: Agent Budget Policy, Agent Rate Limit Policy, Agent Audit Policy
