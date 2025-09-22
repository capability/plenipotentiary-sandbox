You are my architectural assistant.  
I am working on a package called **Plenipotentiary‑Laravel**.  

Here is the context you need to know:

---

### 🔹 Purpose
Plenipotentiary‑Laravel abstracts access to provider APIs (like Google Ads, Facebook Ads, etc.) in a **consistent, testable, vendor‑agnostic** way.

It introduces:
- **Contracts:** strong boundaries (Gateways, Adapters, DTOs, Mappers, Auth, TokenStore, ErrorMappers).  
- **Gateways:** provider‑agnostic CRUD/search entry points.  
- **Adapters:** provider‑specific integrations with SDKs.  
- **Service Providers:** plug‑and‑play DI bindings.  
- **Config overrides:** ability for community/custom adapters.  

---

### 🔹 Folder Structure
```
packages/plenipotentiary-laravel/
  config/pleni.php        # Enables providers + allows overrides

  src/
    Providers/
      PleniCoreServiceProvider.php   # Core-level bindings, provider-agnostic

    Contracts/
      Auth/AuthStrategyContract.php       # Generic PSR-7 auth
      Auth/SdkAuthStrategyContract.php    # Provides authenticated SDK client
      DTO/InboundDTOContract.php
      DTO/OutboundDTOContract.php
      Gateway/ApiCrudGatewayContract.php  # provider-agnostic CRUD contract
      Adapter/ApiCrudAdapter.php          # provider-specific CRUD contract
      Mapper/InboundMapperContract.php
      Mapper/OutboundMapperContract.php
      Error/ErrorMapperContract.php
      Token/TokenStoreContract.php

    Auth/   # Concrete implementations of AuthStrategyContract
      NoopAuthStrategy.php
      TokenAuthStrategy.php
      OAuth2ClientCredentialsStrategy.php
      HmacAuthStrategy.php
      TokenStore/InMemoryTokenStore.php

    Pleni/
      Google/Ads/Contexts/Search/Campaign/
        DTO/
          CampaignInboundDTO.php
          CampaignOutboundDTO.php
        Mapper/
          CampaignInboundMapper.php
          CampaignOutboundMapper.php
        Adapter/
          CampaignApiCrudAdapter.php       # Google Ads provider-specific adapter
        Gateway/
          CampaignApiCrudGateway.php       # Gateway delegating to adapter

      Google/Ads/Shared/
        Auth/GoogleAdsSdkAuthStrategy.php  # Auth wrapper around GoogleAdsClient
        Providers/GoogleAdsServiceProvider.php # Wires Google Ads implementations
        Support/GoogleAdsHelper.php
        Support/GoogleAdsErrorMapper.php

  tests/
    # will contain contract tests + fake sdk client
```

---

### 🔹 Key Class Responsibilities

- **Contracts**
  - `ApiCrudGatewayContract`: Defines CRUD/search for external APIs. Provider‑agnostic.
  - `ApiCrudAdapter`: Defines CRUD at adapter level. Provider‑specific implementation.
  - `InboundDTOContract` / `OutboundDTOContract`: Immutable DTOs for inbound/outbound payloads.
  - `InboundMapperContract` / `OutboundMapperContract`: Translate SDK payloads ↔ DTOs.
  - `ErrorMapperContract`: Maps SDK exceptions into domain exceptions.
  - `AuthStrategyContract`: PSR‑7 request auth.
  - `SdkAuthStrategyContract`: Special strategy for providing authenticated SDK clients.
  - `TokenStoreContract`: Token persistence abstraction.

- **Adapters**
  - Do the **actual provider‑specific SDK calls**.
  - E.g. `CampaignApiCrudAdapter::create()` calls `mutateCampaigns` on GoogleAdsClient wrapper.

- **Gateways**
  - Entry points for the application.
  - Orchestrate cross‑cutting concerns (logging, retries, caching).
  - Delegate to **adapters**.
  - e.g. `CampaignApiCrudGateway::create()` delegates to `CampaignApiCrudAdapter::create()`.

- **DTOs**
  - Outbound = what you send (from your app → provider).  
  - Inbound = what you get back (provider → your app).  
  - Always immutable, always serializable.

- **Mappers**
  - OutboundMapper = map `OutboundDTO` → raw SDK request array.  
  - InboundMapper = map SDK response arrays → `InboundDTO`.

- **Error Handling**
  - Provider SDK errors → mapped into domain exceptions using `ErrorMapperContract`.  
  - Configurable via `pleni.php`.

- **Service Providers**
  - `PleniCoreServiceProvider`: registers contracts (not provider‑specific).  
  - `GoogleAdsServiceProvider`: wires **Google Ads adapter, gateway, mappers, and auth**.

- **Config (`config/pleni.php`)**
  - Enables/disables providers.
  - Lets you override adapters and error mappers (custom/community).  

---

### 🔹 Example Flow
1. App calls `$gateway->create($dto)`.  
2. Gateway validates, logs, and delegates to adapter.  
3. Adapter maps DTO → payload → calls SDK.  
4. SDK returns result → Adapter → mapped to InboundDTO.  
5. Gateway returns DTO to app.  

---

✅ Now you know the structure, roles, and interactions.  
Please retain this entire context in our conversation.  
I will ask you design questions and request next‑step suggestions regarding **Plenipotentiary‑Laravel**.  