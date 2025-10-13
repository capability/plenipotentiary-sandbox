---
sidebar_position: 1
title: Introduction
---

# Plenipotentiary

**Structure, scaffolding, and sanity for API/SDK integrations in Laravel**

![Plenipotentiary Logo](/img/pleni_logo.svg)

A Laravel package helping developers structure API integrations so SDKs, REST APIs, SOAP services and MCP proxies (niche) all feel consistent, testable, and swappable. Provides Gateway/Adapter patterns and Artisan scaffolding. You write the integration code; the package adds validation, error handling, idempotency tracking, and a uniform Result interface across all your external services.

## The Integration Challenge

Your Laravel app integrates with **3-5+ different types of services**: Google Ads (official SDK), Mailchimp (REST), Stripe (official SDK), legacy SOAP services and MCP proxies (niche), internal APIs. Each implemented differently.

**Without a pattern, every integration is a special snowflake:** different error handling, different return types, different testing strategies, different logging approaches. Whether you're a solo developer managing 8 integrations or a team of 10, this mix of different integration types creates maintenance chaos.

## Goal

**A consistent way to work with diverse APIs.**

Provide **architectural patterns and tooling** so that all integrations look consistent from the app's perspective, while allowing full access to underlying APIs.

This means:
- **Consistency** across SDK, REST, SOAP, and (optional) MCP proxy integrations
- **Predictability** in how your app interacts with ANY external service
- **Testability** - same mocking strategy for all integrations
- **Discoverability** - new dev sees Gateway pattern everywhere
- **Swappability** - change providers without touching business logic
- **Focusability** - AI code agents perform best within defined, repeatable patterns. Repetition turns AI output into reliability.

## What This Is NOT

- **Not an "API wrapper for X"** - Wrappers promise complete coverage but deliver 20-40%. They can't keep up with API evolution.
- **Not a "unified API client"** - REST is a philosophy, not a protocol. Universal abstraction is impossible without losing unique provider value.
- **Not complete endpoint coverage** - It provides architecture and patterns. You implement the adapters you need.
- **Not hiding complexity** - Leaky abstractions break under real use. We provide structure, not magic.
- **Not code generation for its own sake** - Generated code is only valuable if it's maintainable and fits your architecture.

> Even API creators struggle to maintain good SDKs. Third-party wrappers are doomed from the start. Plenipotentiary gives you patterns, not promises.

## The Gateway Pattern: Your Architectural Anchor

The **Gateway/Adapter pattern** provides a stable interception point between your application and external services. Once this structure is in place, you gain a **single, consistent location** to layer in production-grade features.

Without it, these concerns scatter across controllers, jobs, and service classes. Impossible to maintain consistently across diverse integrations (SDKs, REST, SOAP).

> The pattern isn't magic; it's discipline. It gives you one place to solve each problem, once, for all integrations.

### What You Can Layer In

Not every integration needs all of these. A simple lookup needs none. A financial integration needs them all.

1. **Validation** - Your app's requirements enforced before the call
2. **Idempotency** - Safe retries, no duplicate charges
3. **Test coverage** - High confidence in production behavior
4. **Persistence** - Audit trail for debugging and compliance
5. **Policy enforcement** - Rate limits, budgets, approval workflows
6. **Error handling** - Retries, circuit breakers, graceful degradation
7. **Observability** - Logging, metrics, distributed tracing

## TL;DR

Think of Pleni like `artisan:make` for third-party APIs: declare the provider, domain, context, and resource and instantly scaffold the DTOs, gateways, adapters and test harness you need. Plenipotentiary provides the contracts. You still implement the adapter logic (it's not magic), but the code now sits in a consistent, testable, tool-friendly structure.

Flysystem-style consistency for APIs, while recognizing not everything should be abstracted. Flysystem works because filesystems share common verbs. APIs don't: REST is philosophy, SDKs are vendor-specific, SOAP is legacy. Plenipotentiary gives them all the same architectural pattern, so your app doesn't care what's underneath.

## Quick Start Examples

Here are some example commands to scaffold different integration patterns:

### Stripe Payment Gateway
```bash
php artisan pleni:make:crud \
  --provider=Stripe \
  --domain=Billing \
  --resource=Customer \
  --with-actions \
  --with-tests
```

### eBay Product Search
```bash
php artisan pleni:make:operation \
  --provider=eBay \
  --domain=Browse \
  --resource=SearchItems \
  --with-controller \
  --with-tests
```

### GitHub API Integration
```bash
php artisan pleni:make:rest \
  --provider=GitHub \
  --domain=API \
  --resource=Repositories \
  --with-requests \
  --with-tests
```

### Internal Admin Tool
```bash
php artisan pleni:make:procedure \
  --provider=InternalAPI \
  --domain=Admin \
  --resource=SendAlert \
  --with-commands
```

### MCP Proxy (Niche: Controlled AI Access)
```bash
php artisan pleni:make:mcp-proxy \
  --server=database \
  --with-budget \
  --with-rate-limit \
  --with-audit \
  --with-tests
```

## AI Code Agents

**AI agents thrive on patterns.** Once you have real-world adapter examples, AI can generate additional adapters that follow your established conventions. With scaffolded tests already in place, it becomes a matter of briefly reviewing AI-generated code rather than writing from scratch; the patterns and test harness provide the guardrails.

## What is a Plenipotentiary?

**plenipotentiary** /ˌplɛnɪpəˈtɛn(t)ʃ(ə)ri/

A person `GATEWAY/ADAPTER`, invested with the full power of independent action on behalf of their government `DOMAIN`, typically in a foreign country `API_PROVIDER`.
