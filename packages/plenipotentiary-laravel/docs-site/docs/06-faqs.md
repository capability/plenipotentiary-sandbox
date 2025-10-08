---
sidebar_position: 6
title: FAQs
---

# FAQs

Things you were about to comment

## Can't all this be done in Saloon?

Saloon is a best-in-class HTTP transport layer with useful plugins. Plenipotentiary focuses on the Gateway pattern and the stability that brings, using Saloon for its proven strengths.

### Saloon gives you:
- HTTP client (Connector/Request classes)
- Authentication (OAuth2, token auth)
- Middleware, retries, rate limiting
- Pagination, caching, mocking
- Recording requests for tests
- Request pools & concurrency

### Plenipotentiary adds:
- **Patterns** - CRUD, Operation, Procedure, REST, MCP Proxy (niche)
- **Layers** - Gateway (stable) vs Adapter (provider-specific)
- **Contracts** - CanonicalDTO, Result monad, Selector
- **Scaffolding** - Artisan commands for rapid setup
- **Integration** - Actions, Jobs, Commands, Tests
- **Cross-cutting** - Idempotency hints, error mapping policies

> Saloon is the HTTP transport layer. Plenipotentiary is the integration architecture layer with patterns, scaffolding and domain contracts. They work together—Plenipotentiary uses Saloon underneath.

## Why Plenipotentiary? I can't even say it, never mind spell it.

Ambassador, Envoy, Emissary, Delegate, Proxy... all the good names are gone. Plenipotentiary captured what it does. Count yourself lucky, I had scattered terms like ForeignState and Ministry throughout an earlier iteration until I accepted that metaphors don't really belong in code. Also, who's going to clash with a Pleni namespace?

## Oh no... it's an API wrapper, isn't it?

Nope. It's an *orchestration + anti-corruption layer*. You still write Adapters; we add guardrails (retries, logging, idempotency, error mapping).

## But this is just what you do when you integrate an API anyway, isn't it?

Exactly! But it's now repeatable, predictable, and testable instead of five ad-hoc services with five retry strategies.

## I could achieve the same thing with a few files in my service layer!

You can. Then again for another service six months later… implemented differently because the new API has its own quirks. By splitting Gateway and Adapter, I keep SDK churn isolated, make the integration testable with mocks, and guarantee things like idempotency and error mapping are always applied. Pleni saves you from your future self.

## What? So you want me to learn your approach AND a new API?

~10 minutes to learn Gateway ↔ Adapter/Operations. After that, you're just writing the same code you'd normally drop into a service class... but in the Adapter, where it's isolated. Pleni isn't an SDK wrapper! You still need to know the provider API. The difference is you only expose what you need, not the entire SDK surface. Over time the community can share common Adapters for basic ops, but the goal is clean contracts and boundaries, not hiding APIs behind another API.

## Er… have you ever heard of Saloon / Guzzle / Laravel HTTP?

We love them. Use them in your Adapter if you like. Pleni isn't an HTTP client; it's the structure around your integration. It doesn't compete with Saloon, it gives Saloon a predictable home.

## Er… have you ever heard of ETL / Pipes / Workflows?

Yes. They're great... when you need a full pipeline orchestrator. Pleni isn't that. It's for exposing just the slice of a big API you need, fast, with guardrails you'll want when things hit production. Use ETL for data pipelines; use Pleni for integrations inside your app.

## Okay. I understand provider, domain, resource... but what's a context?

A context is just a way of saying "this thing works slightly differently depending on how you use it".

Using Google Ads as an example. There are lots of [different campaign types](https://developers.google.com/google-ads/api/docs/campaigns/overview) - Search, Display, Shopping, Performance Max, App, Local Services, and more. They all look like "campaigns", but each has its own rules and setup. Dig deeper and you'll find Ads and Ad Groups behave differently in different campaigns.

In Plenipotentiary, you can model your "Search" marketing strategy (i.e. text ads) as Campaigns, Ad Groups, Ad Group Criterion, and Ads all in one context "Search". Create a new context for your PMAX or Shopping campaigns and you can have different rules, DTOs, and even adapters if you need them. But they all share the same stable Gateway contract, so your app code stays clean. Or don't, the package doesn't impose any structure.

## But... it IS an API wrapper, isn't it?

Okay Yes... in the sense that we all wrap code around APIs/SDKs... but this wrapper adds clean contracts, testability, idempotency, error handling, queuing, extensibility, and the ability to swap out adapters... which I think is a good thing.
