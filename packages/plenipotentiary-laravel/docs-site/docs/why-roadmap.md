---
sidebar_position: 4
title: Why & Roadmap
---

# Why & Roadmap

The story behind Plenipotentiary and where it's heading

## The Elephant in the Room

Let's be brutally honest about what people will think when they first see Plenipotentiary:

### Common First Reactions

**"This is just another API wrapper"**
Like dozens that came before and failed. Remember the Guzzle service wrappers that got abandoned?

**"Why not just use Saloon directly?"**
Saloon is proven, actively maintained, and simpler. Why add another layer?

**"Too much overhead for simple integrations"**
Gateway, Adapter, DTO, Factory, INPUT_SPEC... that's a lot of files for one API call.

**"APIs don't actually change that often"**
Stripe has rock-solid backwards compatibility. Google Ads supports versions for years. Is vendor churn really the problem?

**"This will be abandoned in 6 months"**
One person maintaining scaffolding, patterns, docs, and examples for multiple providers? That's a lot to sustain.

### The Actual Reality

**Not a wrapper—it's architecture**
You still implement the API integration. Plenipotentiary provides structure, not magic. The goal: make Google Ads (SDK) look like Mailchimp (REST) in YOUR system.

**Complements Saloon, doesn't replace it**
Use Saloon for REST calls within your Adapter. Plenipotentiary wraps it in Gateway pattern for consistency across SDK, REST, SOAP, and future transport types.

**Overhead justified for heterogeneous integrations**
1-2 similar APIs? Overkill. 3+ integrations mixing SDKs, REST, and SOAP? Plenipotentiary prevents chaos. It's about consistency across different integration types.

**It's about consistency, not churn**
The real problem: Google Ads SDK, Stripe SDK, Mailchimp REST, and legacy SOAP each look different in YOUR code. Gateway pattern makes them uniform. Vendor churn protection is a bonus, not the goal.

**Built from battle scars, not theory**
This emerged from years of pain—especially Google's AdWords → Ads API migration. It's opinionated because it's lived experience, not academic exercise.

## Who Should Actually Use This?

**Key Insight:** It's about integration diversity, not team size. A solo developer managing 8 different vendor integrations (Google Ads SDK, Mailchimp REST, Stripe SDK, legacy SOAP) benefits more than a 10-person team with 2 similar REST APIs.

### ✅ Good Fit

- Apps with 3-5+ heterogeneous integrations (mixing SDKs, REST, SOAP)
- Solo developers managing 5+ different vendor integrations
- Projects expecting 3+ year lifespans
- Agencies building consistent integration patterns
- Developers who value explicit contracts over magic

### ✗ Probably Overkill

- 1-2 integrations using similar APIs (just use Saloon/SDK directly)
- MVPs and prototypes (premature architecture)
- Small projects with homogeneous integrations (all REST or all SDK)
- Teams allergic to structure and patterns
- Anyone looking for "quick and easy" magic solutions

## The Bottom Line

**Plenipotentiary is not for everyone.** It's deliberately opinionated. It adds ceremony. It requires learning new patterns.

But if you've ever worked on a Laravel app with a dozen different API integrations—some SDKs, some REST, some SOAP nightmares—each implemented differently by different developers over the years, you know the chaos this prevents.

**This isn't innovation.** It's the Gateway pattern (from Domain-Driven Design) applied to Laravel API integrations. Nothing fancy. Just consistent structure for heterogeneous services.

**Will it be actively maintained?** That's the real question. Ambitious frameworks need sustained effort. This is one person's battle-tested approach, shared publicly. Use it, fork it, learn from it, or ignore it—but don't expect it to be the next Laravel Horizon.

**If you're drowning in integration chaos, Plenipotentiary offers a lifeboat. If you're swimming comfortably, you probably don't need it.**

## Why Plenipotentiary exists

I've spent my whole career making one system talk to another. Over those years PHP has improved, frameworks have matured, and our expectations of testing and code robustness have gone up. My earliest attempts, long before Laravel had a foothold, were brittle. I've thrown together integrations quickly; they worked, but they could have been better.

I've also taken some hard knocks. When Google sunset the AdWords API on April 27, 2022 and moved to the Google Ads API, one of my deepest integrations, built 10 years earlier, effectively became a new project just to get back to where I was before. That experience reshaped how I build: better boundaries, cleaner contracts, and isolation from vendor changes.

But the real problem isn't vendor churn—it's chaos. In any Laravel app with multiple integrations, you end up with Google Ads (official SDK), Mailchimp (REST), Stripe (official SDK), legacy SOAP services, and internal APIs—each implemented differently by different developers. Without a pattern, every integration is a special snowflake: different error handling, different return types, different testing strategies, different logging approaches.

There are many ways to skin a cat, and I've tried most of them. What you see here is an opinionated way to make heterogeneous integrations look uniform in YOUR system. Gateway pattern provides the stable interface your app depends on—whether the vendor uses an SDK, REST, SOAP, or something else entirely.

This is not the only way to build integrations. It's not even the best way. It's just my way. If you like it, great! If you keep building integrations a new way every time an API feels different, this opinionated structure will help. If you already have a strong approach, fantastic, share your experience and stick with it. And if you think it's a bad idea, fair enough.

For me, I just wanted a tool that spins up a safe, predictable way to use a small slice of a big API or SDK without reinventing the guardrails every time. This is what I've come up with. And I thought I'd share it.

It's one opinionated approach among many. Suggestions, considerations, critiques, and potential problems are welcome. PRs encouraged.

## Is there a bigger goal?

Yes: **tiny**, shareable adapters with explicit contracts. **Ruthlessly small**... only exposing the high-value slice of an API/SDK, not a mirror of the provider. Guzzle attempted community adapters and it proved to be a maintenance challenge. Perhaps it is a stretch too far. But teams working with the same provider API in different contexts could benefit from **exposing DTO structure** through INPUT_SPEC. Instantly see what data is required, what's validated, and how to construct payloads.

### Zero integration, maximum tooling

Install the adapter, run op through gateway, it fails with a payload that shows the exact DTO (plus expected + violations) and generates the Factory to match. You just fill the values and go.

### Not a community maintenance trap (abstraction-lite)

Community adapters must keep the scope small and task-focused. The goal is to cover the 20% of operations that deliver 80% of value, not the entire API. If you need more, copy the adapter code into your app and extend it. The framework supports it... but that maintenance becomes yours.

### AI-Maintained Adapters?

Would you trust an AI coding agent to maintain an entire API integration for you? Probably not. Would you trust an AI agent to maintain a single CRUD adapter for a Google Ads Campaign resource where each verb has full test coverage and success is deterministic? Perhaps.

Maybe this is the solution to the community maintenance challenge. Small, focused, well-tested adapters that AI agents can reliably maintain and update as provider APIs evolve.

**Reality check:** AI can handle mechanical refactoring (renamed methods, new field types). It can't handle semantic changes requiring business knowledge (which enum value does YOUR business need?). But for the 80% that's mechanical? AI is viable.

## Roadmap: Future Possibilities

Features that aren't here yet but could expand Plenipotentiary's capabilities. These are possibilities, not promises.

### OpenAPI to DTO Generator
Auto-generate CanonicalDTOs and INPUT_SPECs from OpenAPI schemas. Point at a spec, get type-safe DTOs with validation rules already mapped.

### Workflow Integration
First-class support for [Laravel Workflow](https://github.com/laravel-workflow/laravel-workflow). Chain Gateway operations into durable workflows with automatic retry and state management.

### gRPC Transport Support
Extend beyond HTTP/REST. Add gRPC adapters with the same Gateway pattern—stable contracts, swappable transport.

### GraphQL Gateway Pattern
Apply Gateway architecture to GraphQL APIs. Type-safe queries, fragment reuse, and the same anti-corruption layer benefits.

### Event-Driven Adapters
Webhooks and streaming APIs as first-class patterns. Handle inbound events with the same stability guarantees as outbound operations.

### AI-Powered Integration Builder
Describe your integration in natural language, let AI analyze provider docs, generate DTOs, scaffold adapters, write tests, and produce integration logic. The MCP Proxy pattern + Gateway contracts = AI that understands your entire integration surface and can help maintain adapters.
