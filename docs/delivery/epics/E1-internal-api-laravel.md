# Epic E1: Internal API (Laravel) — Customers, Orders, OrderItems

**Goal:** Build a minimal, robust Laravel API that implements the OpenAPI contract for **Customers**, **Orders**, and **OrderItems**. This service is the target the initial Plenipotentiary layer will communicate with.

**Status:** Draft  
**Owner:** <name>  
**Links:**

- Spec: `doc/openapi/providers/acmecart/backoffice/openapi.yaml`
- ADRs: ADR-0001 Generated vs User, ADR-0002 HTTP Driver, ADR-0003 Auth Token Store
- Related Epics: E1 Package Core (MVP), E2 AcmeCart Backoffice Adapter (MVP)

---

## Problem

We need a dependable, testable API to integrate against. Spiking against third-party sandboxes is fragile and slow. A local, contract-first API ensures the orchestration package can be developed TDD with deterministic behavior.

## Scope (In)

- Laravel app endpoints implementing the OpenAPI spec:
  - `/customers` (list, create), `/customers/{customerId}` (get, patch, delete)
  - `/orders` (list, create), `/orders/{orderId}` (get, patch, delete)
  - `/orders/{orderId}/items` (list, create), `/orders/{orderId}/items/{itemId}` (patch, delete)
- Token auth (bearer)
- Cursor pagination (`cursor`, `limit`)
- Idempotency for POST/PATCH via `Idempotency-Key`
- Unified error payloads per spec (404, 422, 429, 5xx)
- Database schema + migrations, Eloquent models (or equivalent), validation, policies
- Feature tests (Pest) proving contract compliance
- Minimal observability (request ID, structured logs)

## Out of Scope

- Business workflows beyond CRUD
- Queues, async processing
- Webhooks
- UI

---

## Deliverables (Stories / Issues)

### E0-01: Project Bootstrap & Infra

- Laravel app skeleton (if not already present under `apps/backend`)
- ENV/config for token auth
- Global middleware: request ID, JSON error shape, idempotency handling
- Health endpoint `/healthz`
- **Acceptance Criteria**
  - [ ] `GET /healthz` returns 200 with app version
  - [ ] Requests require bearer token when enabled
  - [ ] `Idempotency-Key` stored in DB/cache and respected

### E0-02: Data Model & Migrations

- Tables: `customers`, `orders`, `order_items`, `idempotency_keys`
- Constraints: FK `order_items.order_id → orders.id`
- Money stored as **minor units** (integers)
- **Acceptance Criteria**
  - [ ] Migrations create schema matching OpenAPI types
  - [ ] Factories for seed data (Customers, Orders, OrderItems)

### E0-03: Customers Resource

- Controllers, FormRequests, Policies (if needed)
- Routes per spec
- **Acceptance Criteria**
  - [ ] `GET /customers` supports `cursor` + `limit`; returns `PaginatedCustomers`
  - [ ] `POST /customers` validates `CustomerCreate`; 201 + Location header
  - [ ] `GET /customers/{id}` returns `Customer` or 404
  - [ ] `PATCH /customers/{id}` validates `CustomerUpdate`; returns updated `Customer`
  - [ ] `DELETE /customers/{id}` returns 204 or 404
  - [ ] 422 returns ValidationError schema

### E0-04: Orders Resource

- As above for orders
- **Acceptance Criteria**
  - [ ] `GET /orders` supports filter `customer_id`, `cursor`, `limit`
  - [ ] `POST /orders` accepts array of `OrderItemCreate`
  - [ ] `PATCH /orders/{id}` supports status + notes
  - [ ] 201/200/204, 404, 422 behave per spec

### E0-05: OrderItems Sub-resource

- **Acceptance Criteria**
  - [ ] `GET /orders/{orderId}/items` returns `OrderItems`
  - [ ] `POST /orders/{orderId}/items` creates and returns `OrderItem`
  - [ ] `PATCH /orders/{orderId}/items/{itemId}` mutates qty/price
  - [ ] `DELETE /orders/{orderId}/items/{itemId}` removes item
  - [ ] 404s for missing order/item

### E0-06: Idempotency & Rate Limits

- Middleware: store/check `Idempotency-Key` (POST/PATCH)
- Simulated rate limit headers on list endpoints
- **Acceptance Criteria**
  - [ ] Same `Idempotency-Key` + same payload → same result (201→200 with same body)
  - [ ] Different payload with same key → 409 conflict (or 422 with clear error)
  - [ ] `X-RateLimit-Remaining` header present on list endpoints

### E0-07: Error Mapping & Global Handler

- JSON error envelope per spec (`Error`, `ValidationError`)
- Map 404/422/429/5xx
- **Acceptance Criteria**
  - [ ] All errors adhere to `Error` schema
  - [ ] 422 includes field details

### E0-08: Contract Tests (Spec Compliance)

- Pest tests asserting responses match OpenAPI (shape + codes)
- Optional: use an OpenAPI validator in tests
- **Acceptance Criteria**
  - [ ] Happy-path + error-path tests for each endpoint
  - [ ] Pagination behaviors validated (cursor, limit)
  - [ ] Idempotency behaviors validated

---

## Acceptance Criteria (Epic-level)

- [ ] All endpoints implemented and pass Feature tests
- [ ] Error responses match spec
- [ ] Idempotency logic correct and persisted
- [ ] Basic logs (request ID, status, latency)
- [ ] OpenAPI served optionally at `/openapi.json` (generated from file or static)

---

## Dependencies

- OpenAPI spec: `doc/openapi/providers/acmecart/backoffice/openapi.yaml` frozen for v0.1
- ENV secrets for auth
- Database (SQLite for tests; Postgres/MySQL for dev acceptable)

---

## Risks & Mitigations

- **Spec drift** → Treat OpenAPI updates as separate PRs; block endpoint changes until merged
- **Idempotency corner cases** → Enforce checksum of payload in idempotency store
- **Pagination bugs** → Add property-based tests or extra cases around last page / empty page
- **Inconsistent error shapes** → Centralize exception → response mapping in one handler

---

## Test Plan

- **Feature tests** (Pest) for every path/verb per spec (happy + error)
- **Pagination tests**: first page, middle page (has `next_cursor`), last page (null cursor)
- **Idempotency tests**: repeat POST/PATCH with same key + same payload; with different payload
- **Auth tests**: missing/invalid token → 401; valid → proceed
- **Validation tests**: 422 with field details
- **Performance sanity**: N+1 prevented on `GET /orders` (eager load items)

---

## Implementation Notes

- Use **FormRequest** for validation; map directly from OpenAPI schemas
- Use **API Resources** (Laravel) for response shaping (optional); ensure they match spec exactly
- Money as integer minor units; avoid floats
- Cursor pagination via encoded key (e.g., base64 of `(created_at,id)`)
- Global `RequestId` middleware (UUID v4) and logs include it
- Serve `openapi.yaml` as JSON at `/openapi.json` (optional, config-gated)

---

## Definition of Ready (for stories)

- Endpoint path(s) & methods identified
- Request/response schemas final in OpenAPI
- Validation rules derived
- Error cases enumerated

## Definition of Done (for stories)

- Controller + routes + requests + models done
- Feature tests green
- Error mapping verified
- Docs (inline PHPDoc + short note in `doc/guides/testing.md` updated if needed)
