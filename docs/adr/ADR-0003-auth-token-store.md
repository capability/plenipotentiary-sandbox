# ADR-0003: Auth Token Store

- Date: 2025-09-11
- Status: Draft

## Context

OAuth2 and token-based providers require token caching and refresh. Options include DB, cache, or custom implementations.

## Decision

Provide a `TokenStore` contract. Default binding: Laravel cache store with encryption. Allow override in config to DB-backed or custom implementations.

## Consequences

- ✅ Simple for most apps
- ✅ Flexible for multi-tenant or regulated environments
- ⚠️ Refresh complexity must be tested across strategies
