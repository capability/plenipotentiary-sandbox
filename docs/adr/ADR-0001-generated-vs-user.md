# ADR-0001: Generated vs User Split

- Date: 2025-09-11
- Status: Accepted

## Context

We need to generate scaffolding for provider/domain/resource integrations but allow developers to safely customise without losing work on regeneration.

## Decision

Use a **two-layer inheritance model**:

- `Generated/*` abstract base classes are safe to overwrite.
- User-facing classes extend these bases and are never overwritten.
- Only `Generated/*` are touched by `pleni:make --force-generated`.

## Consequences

- ✅ Predictable upgrades
- ✅ Developers can edit safely
- ✅ Generators remain simple
- ⚠️ One level of inheritance (trade-off, but acceptable)
