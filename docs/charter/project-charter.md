---
# 📝 Aider Prompt Template — For Product Charter Tasks

To complete the **Plenipotentiary-Laravel Product Charter**, you can feed aider one step (or a small chunk) at a time. Copy/paste the relevant task into the chat when you’re ready.

---

## 🔑 Ordered Task List

1. **Contracts**
   - Create/update `Contracts/*` for:
     - `CampaignPort` with full CRUD + listAll
     - `AuthStrategy`
     - `TokenStore`
     - `InboundDTOContract` / `OutboundDTOContract`
     - `Mapper` contracts (Inbound/Outbound)
     - `ErrorMapperContract`

2. **DTOs**
   - Implement immutable (`readonly`) `CampaignOutboundDTO` and `CampaignInboundDTO`.

3. **Mappers**
   - Add `CampaignOutboundMapper` and `CampaignInboundMapper` for DTO ↔ array translation.

4. **Adapters**
   - Implement `GoogleAdsCampaignAdapter` using Port contract.
   - Wrap SDK calls, apply outbound/inbound mappers, error handling.

5. **Error Handling**
   - Implement `DefaultErrorMapper`, `ChainErrorMapper`.
   - Add provider-specific (GoogleAdsErrorMapper) and resource-specific (CampaignErrorMapper).

6. **Auth Strategies**
   - Implement `NoopAuthStrategy`, `TokenAuthStrategy`, `OAuth2ClientCredentialsStrategy`, `HmacAuthStrategy`.
   - Implement default `InMemoryTokenStore`.

7. **Configuration**
   - Add `config/pleni.php` with driver maps for:
     - Adapters
     - Error mappers
     - Auth strategies
   - Support overrides via ENV.

8. **Service Provider**
   - Implement `PleniServiceProvider` to resolve adapters, error mappers, and auth strategies from config.
   - Ensure contract binding + fallback behaviors with validation.

9. **Testing**
   - Add **contract tests** for:
     - `CampaignPort` (create/read/update/delete/listAll stubs).
     - Swappable adapters (driver config toggling).
     - Auth strategy behavior (noop, token, OAuth2, hmac).
     - Error mapper chaining.
   - Use **Pest + Testbench**.

10. **Docs**
    - Add `.env` examples.
    - Add usage example snippet in README or charter.
    - Document ADR process (what requires ADR).

---

## ✅ How to Use:
- Copy the specific bullet(s) you want aider to perform.  
- Paste into the chat: *“Please do task X from the template in `project-charter.md` and update these files…”*  

This ensures structured, repeatable, incremental progress.

# Plenipotentiary-Laravel — Project Charter & Technical Design (v1 scope)

> **Purpose**: Capture the *why, what, and how* for an opinionated integration/orchestration layer that accelerates building API integrations in Laravel while staying flexible and testable. This doc is the single source of truth for scope, patterns, guardrails, and the initial roadmap. Use ADRs (Appendix) for change decisions.

---

## 1) Problem Statement

Teams repeatedly spend days/weeks wiring “the same” API integration plumbing: auth flows, retries, pagination, error mapping, DTOs, and scaffolding. Each project re-implements similar patterns with small differences, causing:

* Slow time-to-first-call (TTFC)
* Inconsistent resilience (rate limits, retries, idempotency)
* Fragmented observability & tests
* Risky regeneration (handwritten code gets overwritten)

---

## 2) Proposed Solution (at a glance)

**Plenipotentiary-Laravel** is a pattern framework that scaffolds a **provider/service/context/resource slice** of an integration and supplies common building blocks (auth, DTOs, ports, adapters, error taxonomy, tests).

It is **not a full SDK wrapper**—it exposes only the pieces you need, fast.

**Outcomes**

* TTFC measured in minutes (scaffold → configure → call)
* Contract-tested adapters for consistent behavior
* Configurable adapters (roll-your-own, official, or community)
* Built-in observability and sensible defaults (retries, idempotency)

---

## 3) Scope & Responsibilities

**In scope (package responsibility)**

* Contracts for auth, DTOs, mappers, ports, and error mapping
* Auth strategies (Noop, Token, OAuth2 Client Credentials, HMAC)
* Port + Adapter pattern for resource CRUD/Search
* Config-driven adapter resolution (driver map)
* Error taxonomy & mapper (Transport/Auth/Domain/Validation)
* Test kit (contract tests, HTTP fakes)
* Observability hooks (logs, correlation IDs, optional OpenTelemetry)

**Out of scope (app responsibility)**

* Business workflows, persistence, controllers
* Queue orchestration & scheduling
* UI concerns

---

## 4) Design Principles & Patterns

* **Contracts first**: interfaces live in `Contracts/*`
* **Ports & Adapters**: Ports define ops, Adapters wrap SDK/API calls
* **DTO layering**: `InboundDTO` (external→domain) / `OutboundDTO` (domain→external)
* **Mapper classes** for translation
* **Config-driven driver map**: choose local, official, or community adapters at runtime
* **TDD**: contract tests for any adapter

---

## 5) Folder Structure (package)

```
packages/plenipotentiary-laravel/
  config/pleni.php
  src/
    PleniServiceProvider.php
    Contracts/
      AuthStrategy.php
      CampaignPort.php
      InboundDTOContract.php
      OutboundDTOContract.php
      InboundMapperContract.php
      OutboundMapperContract.php
      ErrorMapperContract.php
    Auth/
      NoopAuthStrategy.php
      TokenAuthStrategy.php
      OAuth2ClientCredentialsStrategy.php
      HmacAuthStrategy.php
    Support/
      GoogleAdsExceptionMapper.php
    Pleni/
      Google/Ads/Contexts/Search/Campaign/
        DTO/
          CampaignInboundDTO.php
          CampaignOutboundDTO.php
        Mapper/
          CampaignInboundMapper.php
          CampaignOutboundMapper.php
        Adapter/
          GoogleAdsCampaignAdapter.php   # default "local" adapter
        Port/
          CampaignPort.php
  tests/
    Contract/… (shared tests)
    ProviderGoogleAds/… (resource tests)
```

---

## 6) Config & Adapter Resolution

Adapters are resolved from a **driver map**:

**config/pleni.php**

```php
return [
    'adapters' => [
        'google' => [
            'ads' => [
                'search' => [
                    'campaign' => [
                        'driver' => env('PLENI_GOOGLE_ADS_SEARCH_CAMPAIGN_DRIVER', 'local'),
                        'map' => [
                            'local' => \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\GoogleAdsCampaignAdapter::class,
                            // future extensions
                            // 'official'  => \Pleni\Adapter\GoogleAds\...\GoogleAdsCampaignAdapter::class,
                            // 'community:vendor1' => \Vendor1\PleniGoogleAds\...\GoogleAdsCampaignAdapter::class,
                            // 'custom'   => \App\Adapters\Google\Ads\...\MyCampaignAdapter::class,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
```

**PleniServiceProvider.php**

```php
$cfg    = config('pleni.adapters.google.ads.search.campaign', []);
$driver = data_get($cfg, 'driver', 'local');
$class  = data_get($cfg, "map.$driver");

if (!is_string($class) || !class_exists($class)) {
    throw new RuntimeException("Adapter for driver [$driver] not found.");
}
if (!is_subclass_of($class, CampaignPort::class)) {
    throw new RuntimeException("[$class] must implement CampaignPort.");
}

$this->app->bind(CampaignPort::class, fn ($app) => $app->make($class));
```

---

## 7) Error Taxonomy & Resilience

* **Transport** (network, 5xx)
* **Auth** (401/invalid)
* **Domain** (business rule)
* **Validation** (422)
* Retry policy: exponential backoff + jitter; respect `Retry-After`
* Idempotency keys for unsafe methods
* Extension points: pluggable retry & idempotency policies

---

## 8) Observability

* Provide hooks via PSR-14/Laravel events
* OpenTelemetry integration (opt-in) with example traces/metrics
* Correlation IDs propagated across requests
* Logs for retries, auth failures, token expiry

---

## 9) Governance

* ADRs required for any contract changes or taxonomy additions
* Minor docs/tests do not require ADRs

---

## 10) Testing Strategy

* **Contract tests**: ensure any adapter bound to `CampaignPort` behaves consistently
* **Adapter swap tests**: assert `CampaignPort` resolves to correct class when config changes
* **Pest + Testbench** for Laravel integration

---

## 11) Milestones

1. **v1.0**: Contracts, auth strategies, config driver map, default Google Ads Campaign adapter (create) + tests
2. **v1.1**: Error mapper, inbound/outbound DTO + mappers, search ops
3. **v1.2**: Pagination helpers, retry policy
4. **v1.3**: Community adapter contributions, official adapter package
5. **v1.4**: Ops milestone — observability, CI/CD, DX improvements

---

## 12) Definition of Done

* Tests pass (unit + contract)
* Config driver map supports swap between adapters
* Docs updated with usage snippet
* No breaking changes to contracts without ADR

---
