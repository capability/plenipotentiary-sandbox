# Plenipotentiary‑Laravel — Project Charter & Technical Design (v0.2)

> **Purpose**: Capture the _why, what, and how_ for an opinionated integration/orchestration layer that accelerates building API integrations in Laravel while staying flexible and testable. This doc is the single source of truth for scope, patterns, guardrails, and the initial roadmap. Use ADRs (Appendix) for change decisions.

---

## 1) Problem Statement

Teams repeatedly spend days/weeks wiring “the same” API integration plumbing: auth flows, retries, pagination, error mapping, DTOs, and scaffolding. Each project re‑implements similar patterns with small differences, causing:

- Slow time‑to‑first‑call (TTFC)
- Inconsistent resilience (rate limits, retries, idempotency)
- Fragmented observability & tests
- Risky regeneration (handwritten code gets overwritten)

## 2) Proposed Solution (at a glance)

**Plenipotentiary‑Laravel** is an opinionated package that generates a provider/domain/resource integration skeleton and supplies common building blocks (auth, pagination, retry, error taxonomy, transformers, tests). It uses a **Generated vs User** code split to allow safe regeneration and targeted customization.

**Outcomes**

- TTFC measured in minutes (scaffold → configure → call)
- Contract‑tested adapters for consistent behavior
- Built‑in observability and sensible defaults (retries, backoff, idempotency)
- Optional OpenAPI assist; no hard lock‑in

---

## 3) Scope & Responsibilities

**In scope (package responsibility)**

- CLI scaffolding: `pleni:make Provider=… Domain=… Resource=… [--auth --client --with-tests]`
- Provider/domain/resource structure with DTOs (Inbound/Outbound), Transformers, Repositories, Services, optional Console, and a Provider ServiceProvider
- Auth strategies (Token, Basic, OAuth2 Client Credentials, OAuth2 3‑legged) behind a contract
- HTTP concerns: retries (exp+jitter), rate‑limit handling, idempotency key injection, pagination helpers
- Error taxonomy & mapper (Transport/Auth/Domain/Validation)
- Config surface and env bindings
- Test kit: Testbench setup, HTTP fakes, contract test bases
- Observability hooks: structured logs, correlation IDs, optional OpenTelemetry spans

**Out of scope (app responsibility)**

- Business workflows, persistence, controllers (except optional OAuth/webhook endpoints)
- Queue orchestration and scheduling
- UI concerns (frontend rendering, resources)

---

## 4) Design Principles & Patterns

- **Ports & Adapters (Hexagonal)**: Contracts in `Contracts/*`; provider/domain implementations in `Pleni/{Provider}/{Domain}/…`
- **Generated vs User split**: Regen‑safe base classes in `Generated/*`; user‑editable finals extend them
- **Template Method (shallow inheritance)**: 1‑level override points (e.g., `mapInbound`, `mapOutbound`, `onError`)
- **Composition over inheritance** for variability: inject `AuthStrategy`, `Paginator`, `Transformer`, `ErrorMapper`
- **PSR first where cheap**: PSR‑3 (log), PSR‑7/17/18 (HTTP), PSR‑16 (cache), PSR‑11 (container in bridges)
- **TDD**: Feature tests for API sandbox; unit/contract tests in package; Testbench for Laravel boot
- **Fail‑safe regeneration**: headers + checksums; only `Generated/*` overwritten with `--force-generated`

---

## 5) Folder Structure (package)

```
packages/plenipotentiary-laravel/
  config/pleni.php
  src/
    PleniServiceProvider.php
    Console/
      PleniMakeCommand.php
    Contracts/
      AuthStrategy.php
      TokenStore.php
      Paginator.php
      ErrorMapper.php
      Transformer.php
      // optional resource contracts e.g. CampaignRepository.php
    Auth/
      TokenAuth.php
      BasicAuth.php
      OAuth2ClientCredentials.php
      OAuth2ThreeLegged.php
    Http/
      Middleware/RetryMiddleware.php
      Middleware/RateLimitMiddleware.php
      IdempotencyKeyGenerator.php
    Support/
      Arr.php
      Clock.php
      Tracing.php
      Exceptions/{Transport,Auth,Domain,Validation}Exception.php
    Pleni/
      {Provider}/
        {Domain}/                     // Ads, Analytics, BigQuery, MerchantCenter
          {Resource}/                 // Campaign, Keyword, Dataset, Product, ...
            DTO/
              Inbound/…
              Outbound/…
            Transformers/
              Generated/Generated{Resource}Transformer.php
              {Resource}Transformer.php
            Repositories/
              Generated/Generated{Resource}Repository.php
              {Resource}Repository.php
            Services/
              Generated/Generated{Resource}Service.php
              {Resource}Service.php
            Console/
              Generated/Generated{Resource}Command.php
              {Resource}Command.php
          Providers/ServiceProvider.php   // binds all {Domain} resources for this Provider
        Shared/
          Auth/* (provider-specific)
          Transformers/*
          ErrorMaps/*
  tests/
    Package/* (config, command, auth, middleware)
    Provider{X}/* (contract tests per resource)
  stubs/* (writer templates for scaffolding)
  phpunit.xml
```

---

## 6) CLI Scaffolding

**Command**: `pleni:make`

```
php artisan pleni:make Provider=Google Domain=Ads Resource=Campaign \
  --auth=oauth2|token|basic \
  --client=laravel|saloon \
  --base-url=https://googleads.googleapis.com \
  --with-tests \
  --dry-run \
  --force-generated \
  --path=./ (for tests)
```

**Behavior**

- Writes `Generated/*` and user classes; **never** overwrites user files
- Adds headers + checksums to generated files
- Prints config snippet for `config/pleni.php`
  **Behavior**
- Writes `Generated/*` and user classes; **never** overwrites user files
- Adds headers + checksums to generated files
- Prints config snippet for `config/pleni.php`

---

## 7) Auth Design

- Contract: `AuthStrategy::apply(RequestInterface $request, array $context = []): RequestInterface`
- Built‑ins: Token, Basic, OAuth2 CC, OAuth2 3L (with TokenStore)
- Provider‑specific wrappers live in `Pleni/{Provider}/Shared/Auth/*`
- Optional routes for interactive OAuth (disabled by default)

---

## 8) Error Taxonomy & Resilience

- **Transport** (network, 5xx), **Auth** (401/invalid), **Domain** (business rule), **Validation** (422)
- Retry policy: exponential backoff + jitter; respect `Retry‑After` when present
- Rate‑limit: sleep/queue based on headers (provider‑specific mappers allowed)
- Idempotency keys for unsafe methods where supported

---

## 9) Observability

- Structured logs with correlation IDs
- Optional OpenTelemetry spans around outbound HTTP (config flag)
- Minimal metrics hooks (calls, errors, retries, RL waits)

---

## 10) Testing Strategy

- **Package**: Pest + Orchestra Testbench

  - Config publish/merge tests
  - `pleni:make` generation tests (with `--path`)
  - Auth strategy units; middleware units (retry/RL)

- **Contract tests**: abstract suites that adapter repos must pass (CRUD, pagination, error mapping)
- **API sandbox (separate app)**: Feature tests for demo CRUD endpoints (Customers, Orders, OrderItems)

---

## 11) Milestones

1. **MVP (v0.1)**: Service provider, config, `pleni:make`, Token auth, retry middleware, basic contract tests
2. **v0.2**: OAuth2 CC + TokenStore, pagination helpers, error mapper, observability hooks
3. **v0.3**: OAuth2 3L (optional routes), idempotency keys, record‑and‑replay test helper
4. **v0.4**: OpenAPI import assist, Saloon driver, first public adapter example

---

## 12) Risks & Mitigations

- **Over‑abstraction** → Keep shallow inheritance and few hooks; ship examples
- **Framework drift** → Testbench matrix Laravel 11; pin constraints conservatively
- **DX complexity** → Defaults that “just work”; optional features are opt‑in
- **Auth edge cases** → Provider‑specific wrappers and contract tests

---

## 13) Definition of Done (per feature)

- Tests passing (unit + contract + generation)
- Docs updated (README + usage snippet)
- No breaking changes to public contracts without ADR
- Observability and error mapping covered

---

## 14) Agile/Documentation Approach

Use lightweight, living docs:

- **This Project Charter & Technical Design** (single owning page)
- **ADRs (Architecture Decision Records)** for consequential choices; 1–2 pages each
- **CHANGELOG** per release
- **README** for quickstart and commands

### ADR Template (Appendix A)

```
# ADR-000X: Title
- Date: YYYY-MM-DD
- Status: Proposed | Accepted | Superseded by ADR-000Y
- Context: What problem/forces drive this decision?
- Decision: What is chosen and why?
- Consequences: Positive, negative, risks, follow-ups
```

### Example — ADR-0001: Generated vs User Split (Accepted)

- **Context**: Need safe regeneration without merging user code
- **Decision**: One-level `Generated/*` abstract base extended by user `final` classes; only `Generated/*` are overwritten via `--force-generated`
- **Consequences**: Predictable upgrades; small inheritance surface; composition for variability

---

## 15) Open Questions

- Which HTTP driver default: Laravel HTTP or Saloon? (MVP: Laravel HTTP)
- Provide optional example Jobs or leave to README snippet?
- Which providers to ship as examples first? (e.g., Google Ads Campaign, BigCommerce Catalog)

---

## 16) Next Actions

- Scaffold package skeleton and `pleni:make`
- Add first contract tests and a basic provider (Google/Ads → Campaign)
- Wire Composer path repo from sandbox app; run first end‑to‑end call

---

## 17) Wiring & Composer setup (Sandbox)

**Repo layout**

```
plenipotentiary-sandbox/
  apps/
    backend/                 # Laravel app
    frontend/
  packages/
    plenipotentiary-laravel/ # the package
```

**packages/plenipotentiary-laravel/composer.json** (essentials)

```
{
  "name": "capability/plenipotentiary-laravel",
  "description": "Opinionated integration/orchestration layer for Laravel.",
  "type": "library",
  "license": "MIT",
  "require": {
    "php": "^8.2",
    "illuminate/support": "^11.0",
    "psr/http-message": "^2.0",
    "illuminate/http": "^11.0"
  },
  "autoload": {
    "psr-4": {
      "Plenipotentiary\\Laravel\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Plenipotentiary\\Laravel\\Tests\\": "tests/"
    }
  },
  "extra": {
    "laravel": {
      "providers": [
        "Plenipotentiary\\Laravel\\PleniServiceProvider"
      ]
    }
  },
  "require-dev": {
    "orchestra/testbench": "^9.0",
    "pestphp/pest": "^3.0",
    "pestphp/pest-plugin-laravel": "^3.0",
    "brianium/paratest": "^7.8.4",
    "phpunit/phpunit": "^11.2"
  },
  "minimum-stability": "dev",
  "prefer-stable": true,
  "config": {
    "allow-plugins": {
      "pestphp/pest-plugin": true
    }
  }
}
```

**Install & wire**

```
cd apps/backend
composer require your-vendor/plenipotentiary-laravel:"dev-main"
php artisan vendor:publish --provider="Plenipotentiary\Laravel\PleniServiceProvider" --tag=config
```

**Quick smoke test**

TODO

```
php artisan pleni:make Provider=Google Domain=Ads Resource=Campaign --with-tests
# confirm files under packages/plenipotentiary-laravel/src/Pleni/Google/Ads/Campaign/...
```
