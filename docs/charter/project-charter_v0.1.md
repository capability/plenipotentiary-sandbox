# project-charter.md — Plenipotentiary-Laravel

## 1. Purpose
Plenipotentiary‑Laravel abstracts access to provider APIs (Google Ads, Facebook Ads, etc) in a **consistent, testable, and provider‑agnostic** way.  

It solves the problem of **vendor lock, duplication, and difficulty testing** by introducing:
- **Contracts** → strong boundaries for Gateways, Adapters, Auth, DTOs, and Mappers.  
- **Gateways** → provider‑agnostic entry points exposing CRUD/search.  
- **Adapters** → provider‑specific integrations with SDKs or APIs.  
- **Service Providers** → plug‑and‑play bindings per provider.  
- **Config overrides** → enable official, community, or custom implementations without touching internal code.

---

## 2. Goals
1. **Provider Independence**  
   Applications depend only on `ApiCrudGatewayContract` and DTOs, never on SDKs directly.  

2. **Testability**  
   Providers / Adapters can be swapped with fakes via `SdkClientContract`. Tests run with no network calls.  

3. **Extensibility**  
   - Official providers (Google Ads) ship with this package.  
   - Community providers ship as external packages with their own `ServiceProvider`.  
   - Custom adapters/mappers can be declared in `config/pleni.php`.  

4. **Consistency**  
   All integrations present the same CRUD API surface: `create`, `read`, `update`, `delete`, `listAll`.

5. **Separation of Concerns**  
   - Gateways deal with orchestration, logging, retries.  
   - Adapters deal with SDK translation and low‑level API details.  
   - Mappers keep payload translation clean and consistent.  

---

## 3. Scope
- **Core**  
  - Contracts: `ApiCrudGatewayContract`, `ApiCrudAdapter`, DTOs, Mappers, Auth, TokenStore, ErrorMapper.  
  - Generic `PleniCoreServiceProvider`.  

- **Google Ads (official provider)**  
  - Auth strategy (`GoogleAdsSdkAuthStrategy`)  
  - Adapters, Gateways, Mappers for Campaigns.  
  - `GoogleAdsServiceProvider` binds contracts → implementations.  
  - Helpers (`GoogleAdsHelper`, error mappers).  

- **Config**  
  - `config/pleni.php` declares which providers to enable and allows adapter/error mapper overrides.

- **Testing**  
  - Fake `SdkClientContract` for offline tests.  
  - Contract‑level verification ensures adapters comply.  

---

## 4. Architecture Overview

### Layers
- **Client App Layer**  
  Calls `ApiCrudGatewayContract` (e.g. `CampaignApiCrudGateway`).  

- **Gateway Layer**  
  Orchestrates business logic, delegates to adapter.  

- **Adapter Layer**  
  Translates DTOs ↔ SDK calls, applies auth, maps errors.  

- **Mapper Layer**  
  Pure transformations between DTOs ↔ arrays.  

- **Auth Layer**  
  Pluggable strategies: Token, OAuth2, HMAC, Noop, SDK‑based.  

---

## 5. Provider Model
- **Core Provider:** `PleniCoreServiceProvider` (agnostic contracts).  
- **Provider‑Specific Providers:** e.g. `GoogleAdsServiceProvider`.  
- **Community Providers:** custom packages declare their own `ServiceProvider`.  
- **Custom Adapters:** bound in local app config (`config/pleni.php['adapters']`).  

---

## 6. Guiding Principles
- Prefer **contracts > implementations** in dependencies.  
- Keep **DTOs immutable**, serialization‑first.  
- **Never leak SDK objects** outside the adapter layer.  
- **Centralize error mapping** via `ErrorMapperContract`.  
- **Be Laravel‑native**: service providers, config‑driven overrides, container‑friendly.  

---

## 7. Current Capabilities vs Roadmap

| Area        | Status              | Notes / Next Steps                     |
|-------------|---------------------|----------------------------------------|
| Core Contracts | ✅ Implemented | Covers Gateway, Adapter, DTO, Mapper, Auth, Error, TokenStore |
| Auth Strategies | ✅ Implemented | Token, HMAC, OAuth2 Client Credentials, Noop |
| Token Store | ✅ Implemented | InMemory store, pluggable |
| Google Ads: Campaign | ✅ Implemented | Outbound+Inbound DTO, Mapper, Adapter, Gateway |
| Google Ads: Error Mapping | ✅ Basic | Extend mapping for granular errors |
| Testing Support | 🚧 Partial | FakeSdkClient to be formalized |
| Config System | ✅ Implemented | Providers_enabled + overrides |
| Service Providers | ✅ Implemented | Core + GoogleAds |
| Other Google Ads Resources (AdGroups, Ads, Keywords, etc.) | ⏳ Roadmap | Structure already ready for extension |
| Community Providers | Planned | Packages can ship their own ServiceProviders |
| Custom Adapters | ✅ Supported | Via `config/pleni.php` overrides |

---

✅ This charter sets a coherent vision: **Plenipotentiary‑Laravel = clean boundary between your app and vendor APIs, with configurability for official/community/custom extensions**.
