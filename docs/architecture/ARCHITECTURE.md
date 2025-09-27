# Folder Structure & Responsibilities

## Contracts/

- Defines framework-agnostic contracts (interfaces) for adapters, gateways, repositories, auth, idempotency, error mapping, etc.  
- Purpose: enforce clean Dependency Inversion so that provider SDKs (Google Ads, FB, etc.) are always hidden behind contracts.  
- Examples:  
  - `ApiCrudAdapterContract`, `ApiCrudGatewayContract` – separation between provider-specific IO vs provider-agnostic orchestration.  
  - `SpecContract` – cheap preflight validation descriptions.  
  - `AuthStrategyContract` vs `SdkAuthStrategyContract` – distinction between simple HTTP header auth vs handing over a full SDK client.  
  - `IdempotencyStore`, `IdempotencyHints` – encapsulate idempotent retry/race protection.  
  - `ErrorMapperContract` – ensure SDK exceptions don’t leak raw details.

---

## Auth/

- Implements authentication strategies that comply with `AuthStrategyContract`.  
- Variants:  
  - `HmacAuthStrategy`, `TokenAuthStrategy`, `OAuth2ClientCredentialsStrategy`, `NoopAuthStrategy`.  
- Reason: Strategy pattern so that different APIs can easily swap auth without changing consumers.

---

## Idempotency/

- `CacheIdempotencyStore` – uses Laravel cache to persist fingerprints & tombstones.  
- Reason: provide retry safety and race prevention across Create/Update/Delete.

---

## Support/

- `Result`, `Page`, `OperationDescription`, `ValidationException`, `Logging`.  
- All operations return a `Result` (monadic style: ok/err/invalid).  
- Reasons:  
  - Provides a consistent return contract, instead of throwing unchecked exceptions everywhere.  
  - Allows invalid/err handling to be explicit at gateways and higher layers.  
- `OperationDescription` / `ValidationException` – useful for describing validation rules (can be surfaced in APIs / UIs).  
- Logging helpers (`LoggingService`, `Redactor`) ensure safe, redacted log output.

---

## Traits/

- `HandlesEloquentCrud` for repositories.  
- Standardises CRUD across repositories using trait composition.  
- Reason: keeps repo implementations extremely thin.

---

## Pleni/Google/Ads/…

This is the Google Ads provider integration module.

- **Contexts/Search/Campaign**  
  - `DTO/CampaignCanonicalDTO` – the canonical unified representation of a Campaign (provider-agnostic).  
  - `Key/CampaignSelector`, `CampaignSelectorKind` – selector objects for reads/deletes.  
  - `Adapter/...` – provider-specific input/output mapping (separated into Create, Update, Delete, Read sub-modules).  
  - `Gateway/CampaignApiCrudGateway` – orchestrates logging, idempotency, and delegates to the adapter.  
  - `Repository/EloquentCampaignRepository` + contract – persistence abstraction on app side.  
- **Shared/**  
  - `Auth` – wraps Google Ads SDK authentication (using env vars).  
  - `Lookup` – tiny DSL (`Lookup`, `Criterion`, `Op`, `Dir`, `Sort`) + `Gaql\QueryBuilder` that maps canonical fields into GAQL SQL for searches.  
  - `Providers/GoogleAdsServiceProvider` – wires everything together in the Laravel IoC container.  
  - `Support/GoogleAdsErrorMapper` and `GoogleAdsHelper` – centralise error handling and config conversions.

---

# Core Patterns & Reasons

1. **Adapter/Gateway Separation**  
   - Adapter: talks to Google’s SDK (constructs requests, parses responses).  
   - Gateway: provider-agnostic façade – handles logging, idempotency, error mapping.  
   - Why: keeps SDK code isolated and enforces clean boundaries.

2. **Canonical DTOs**  
   - Example: `CampaignCanonicalDTO` (provider-neutral).  
   - Why: allows multiple provider adapters (Google Ads, FB Ads, etc.) to map into a common representation at service layer.

3. **Spec Contracts**  
   - Lightweight local preflight validation via `Spec::preflight()` and `OperationDescription`.  
   - Why: catch trivial errors early without SDK calls, provide machine-readable validation metadata.

4. **Idempotency Layer**  
   - Enforced in Gateway for create/update/delete.  
   - Why: ensures retries (network failures, replays) don’t duplicate campaigns or delete twice.

5. **Error Mapping**  
   - `GoogleAdsErrorMapper` normalises `ApiException`/quota/auth errors into domain exceptions.  
   - Why: avoid leaking raw provider types across the boundary.

6. **Lookup DSL + QueryBuilder**  
   - Canonical search criteria (`Lookup`, `Criterion`, `Sort`) compiled into GAQL.  
   - Why: keeps query construction safe, whitelists fields, and supports multiple providers.

7. **Result Monad**  
   - All operations yield `Result::ok()`, `Result::err()`, or `Result::invalid()`.  
   - Why: explicit, typed error handling (composition is easier than exceptions).

8. **Dependency Injection / IoC Binding**  
   - Everything is wired in `GoogleAdsServiceProvider`.  
   - Why: centralises provider wiring, allows mocks/swaps during testing.

---

# Gateway vs Adapter — Clear Division of Responsibility

A key design principle in this codebase is the strict boundary between **Gateways** and **Adapters**.


## Gateway

**Role**  
Gateways are the predictable entry point that the local domain code calls into.  
They ensure all external provider operations are wrapped in a consistent shape:

- Always return `Result` objects (`ok | err | invalid`)  
- Always funnel through consistent logging, queueing hooks, and idempotency checks  
- Centralise retry, event dispatching, and observability  

**Perspective**  
Gateways don’t know (or care) about Google Ads SDK versions, HTTP details, or request building syntax.  
All they do is trust the Adapter to carry out the requested action.

**Analogy**  
Think of the Gateway as the **stable façade/port** your application team codes against.

## Adapter

**Role**  
Adapters are where all provider-specific complexity lives:

- Knows the Google Ads SDK types and GAQL query syntax  
- Translates DTOs into provider request objects  
- Parses SDK responses into canonical form  
- Handles external communication (`mutateCampaigns`, `search()`, etc.)  

**Perspective**  
You can often lift raw SDK examples directly into an Adapter with minimal modification.  
If working against REST APIs instead, this layer could just as easily issue HTTP requests via something like Saloon.

**Analogy**  
Think of the Adapter as a **translator or proxy**, completely encapsulating the provider’s language/API.

## Why Separate Them?

- The Gateway code is provider-agnostic, so anything in Laravel (jobs, controllers, CLI commands) can queue/log/monitor operations without knowing the provider details.  
- The Adapter is provider-specific, so new implementations (different SDK version, or swapping to a REST client like Saloon) can be dropped in with zero changes to the domain code.  
- This keeps the core domain predictable and lets provider details evolve independently.

## TL;DR:

- **Gateway** = Safe contract, standardised ops, local integration point.  
- **Adapter** = Provider expertise, SDK/HTTP plumbing, raw interaction details.  

This separation is what makes the system both extensible and resilient to SDK churn.

---

# Summary

The codebase applies classic Hexagonal / Clean Architecture patterns:

- Ports & Adapters (Contract + Adapter + Gateway) for SDK isolation.  
- Canonical DTOs + Specs for uniform validation and cross-provider abstraction.  
- Result monads for predictable outcome handling.  
- Idempotency protection for safe retries.  
- Strategy pattern in Auth for flexible auth models.  
- Dependency Injection + Service Providers for wiring.

**Together, this ensures extensibility (multiple providers), testability (swap contracts), and safety (idempotency + validation).**
