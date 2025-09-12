# ADR-0002: HTTP Driver Choice

- Date: 2025-09-11
- Status: Accepted

## Context

We must choose a default HTTP client strategy for repositories. Options: Laravel HTTP client, Saloon, Guzzle (PSR-18).

## Decision

- Default to **Laravel HTTP client** for MVP (out-of-the-box for Laravel apps).
- Support **Saloon** as an optional driver (configurable).
- Keep contracts PSR-18 friendly to allow future adapters.

## Consequences

- ✅ Easy adoption for Laravel developers
- ✅ Room for advanced Saloon users
- ⚠️ Symfony users will need a bridge later
