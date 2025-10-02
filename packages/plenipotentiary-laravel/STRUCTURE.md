# Package Structure

## Overview

This document shows the current structure of the plenipotentiary-laravel package after recent refactoring.

## Root Structure

```
src/
├── Auth/                           # Framework-level auth strategies
├── Contracts/                      # Core contracts/interfaces
├── Exceptions/                     # Core exceptions
├── Idempotency/                    # Idempotency implementations
├── Pleni/                          # Provider-specific code
├── Providers/                      # Core service providers
└── Support/                        # Shared utilities and helpers
```

## Core Contracts

```
Contracts/
├── Adapter/
│   ├── AdapterVerbContract.php              # Base adapter verb interface
│   ├── CrudAdapterContract.php              # CRUD adapter interface (SDK-based)
│   ├── ProcedureAdapterContract.php         # RPC-style adapter interface
│   └── RestAdapterContract.php              # REST adapter interface
├── Auth/
│   ├── AuthStrategyContract.php             # Base auth strategy
│   └── SdkAuthStrategyContract.php          # SDK-specific auth
├── Client/
│   ├── HttpProviderClientContract.php       # HTTP client interface
│   └── ProviderClientContract.php           # Provider client base
├── DTO/
│   └── CanonicalDTOContract.php             # Canonical DTO interface
├── Error/
│   └── ErrorMapperContract.php              # Error mapping interface
├── Gateway/
│   ├── ApiCrudGatewayContract.php           # CRUD gateway interface
│   ├── ApiRpcGatewayContract.php            # RPC gateway interface (deprecated)
│   ├── ProcedureGatewayContract.php         # Procedure gateway interface
│   └── RestGatewayContract.php              # REST gateway interface
├── Idempotency/
│   ├── EndpointIdempotencyHints.php         # Endpoint-level hints
│   ├── IdempotencyHints.php                 # Base hints interface
│   └── IdempotencyStore.php                 # Idempotency storage
├── Repository/
│   └── BaseRepositoryContract.php           # Repository pattern base
├── Selector/
│   └── SelectorContract.php                 # Selector/query builder
└── Token/
    └── TokenStoreContract.php               # Token storage interface
```

## Provider Structure

### Pattern: `Pleni/{Provider}/{Domain}/`

Each provider follows this structure:

```
Pleni/{Provider}/{Domain}/
├── Contexts/                       # Context-specific implementations
│   └── {Context}/                  # e.g., Search, Billing, Default
│       ├── {Resource}/             # Resource-based (CRUD) operations
│       │   ├── Adapter/            # Provider-specific CRUD adapter
│       │   ├── DTO/                # Canonical DTOs
│       │   ├── Gateway/            # Stable gateway facade
│       │   ├── Repository/         # Persistence layer
│       │   ├── Selector/           # Query builder
│       │   ├── Support/            # Context helpers
│       │   └── Providers/          # Service provider
│       ├── Procedure/              # RPC-style operations
│       │   ├── Actions/            # Action classes
│       │   ├── Commands/           # Artisan commands
│       │   └── Providers/          # Service provider
│       └── Requests/               # Laravel request objects
└── Shared/                         # Shared provider-level code
    ├── Auth/                       # Provider auth strategy & client
    ├── Providers/                  # Provider-level service provider
    ├── Support/                    # Config, helpers, error mappers
    └── Transfer/                   # Transfer layer (API communication)
        ├── Procedure/              # RPC-style connectors
        │   ├── {Provider}{Domain}ProcedureGateway.php
        │   ├── {Provider}{Domain}ProcedureAdapter.php
        │   ├── {Provider}{Domain}ProcedureConnector.php
        │   └── {Provider}{Domain}DynamicRequest.php
        └── Rest/                   # RESTful connectors
            ├── {Provider}{Domain}RestGateway.php
            ├── {Provider}{Domain}RestAdapter.php
            └── {Provider}{Domain}RestConnector.php
```

## Implemented Providers

### 1. Google Ads (SDK-based CRUD)

```
Pleni/Google/Ads/
├── Contexts/
│   └── Search/
│       ├── Campaign/                          # Resource: Campaign
│       │   ├── Adapter/
│       │   │   ├── CampaignCrudAdapter.php    # Main adapter
│       │   │   ├── CampaignCreate.php         # Create verb
│       │   │   ├── CampaignRead.php           # Read verb
│       │   │   ├── CampaignReadMany.php       # ReadMany verb
│       │   │   ├── CampaignUpdate.php         # Update verb
│       │   │   ├── CampaignDelete.php         # Delete verb
│       │   │   └── CreateSupport/
│       │   │       └── CampaignCreateBudget.php
│       │   ├── DTO/
│       │   │   └── CampaignCanonicalDTO.php
│       │   ├── Gateway/
│       │   │   └── CampaignCrudGateway.php
│       │   ├── Repository/
│       │   │   ├── CampaignRepositoryContract.php
│       │   │   ├── EloquentCampaignRepository.php
│       │   │   ├── InMemoryCampaignRepository.php
│       │   │   └── MongoCampaignRepository.php
│       │   ├── Selector/
│       │   │   └── CampaignSelector.php
│       │   ├── Support/
│       │   │   └── CampaignIdempotencyHints.php
│       │   └── Providers/
│       │       └── CampaignServiceProvider.php
│       └── Requests/
│           └── SearchCampaignsRequest.php
└── Shared/
    ├── Auth/
    │   ├── GoogleAdsSdkAuthStrategy.php
    │   └── GoogleAdsSdkClient.php
    ├── Lookup/                                # Google Ads-specific query builder
    │   ├── Criterion.php
    │   ├── Dir.php
    │   ├── Gaql/
    │   │   └── QueryBuilder.php
    │   ├── Lookup.php
    │   ├── Op.php
    │   ├── Page.php
    │   └── Sort.php
    ├── Providers/
    │   └── GoogleAdsServiceProvider.php
    ├── Support/
    │   ├── GoogleAdsConfig.php
    │   ├── GoogleAdsDefaults.php
    │   ├── GoogleAdsErrorMapper.php
    │   └── GoogleAdsHelper.php
    └── Transfer/
        ├── Procedure/
        │   ├── GoogleAdsProcedureGateway.php
        │   ├── GoogleAdsProcedureAdapter.php
        │   ├── GoogleAdsProcedureConnector.php
        │   └── GoogleAdsDynamicRequest.php
        └── Rest/
            ├── GoogleAdsRestGateway.php
            ├── GoogleAdsRestAdapter.php
            └── GoogleAdsRestConnector.php
```

### 2. Stripe (REST-based CRUD)

```
Pleni/Stripe/Api/
├── Contexts/
│   └── Billing/
│       └── Customer/                          # Resource: Customer
│           ├── Adapter/
│           │   ├── CustomerCrudAdapter.php    # Main adapter
│           │   ├── CustomerCreate.php         # Create verb
│           │   ├── CustomerRead.php           # Read verb
│           │   ├── CustomerUpdate.php         # Update verb
│           │   └── CustomerDelete.php         # Delete verb
│           ├── DTO/
│           │   └── CustomerCanonicalDTO.php
│           ├── Gateway/
│           │   (Empty - Gateway at Shared level)
│           └── Selector/
│               └── CustomerSelector.php
└── Shared/
    ├── Auth/
    │   └── StripeRestAuthStrategy.php
    ├── Providers/
    │   └── StripeServiceProvider.php
    ├── Support/
    │   ├── StripeConfig.php
    │   └── StripeErrorMapper.php
    └── Transfer/
        └── Rest/
            ├── StripeApiRestConnector.php
            └── (Gateway/Adapter at this level)
```

### 3. eBay Browse (Procedure-based, non-CRUD)

```
Pleni/eBay/Browse/
├── Contexts/
│   └── Default/                               # Context: Default (non-resource)
│       ├── ProcedureConnector/
│       │   ├── Actions/
│       │   │   └── SearchItemsAction.php
│       │   ├── Commands/
│       │   │   └── SearchItemsCommand.php
│       │   └── Providers/
│       │       └── eBayBrowseServiceProvider.php
│       └── Requests/
│           └── SearchItemsRequest.php
└── Shared/
    ├── Auth/
    │   ├── eBaySdkAuthStrategy.php
    │   └── eBaySdkClient.php
    ├── Support/
    │   ├── EbayConfig.php
    │   └── eBayErrorMapper.php
    └── Transfer/
        ├── Procedure/
        │   ├── eBayBrowseProcedureGateway.php
        │   ├── eBayBrowseProcedureAdapter.php
        │   ├── eBayBrowseProcedureConnector.php
        │   └── eBayBrowseDynamicRequest.php
        └── Rest/
            ├── eBayBrowseRestGateway.php
            ├── eBayBrowseRestAdapter.php
            └── eBayBrowseRestConnector.php
```

### 4. OpenAI (Procedure-based, non-CRUD)

```
Pleni/OpenAI/Api/
├── Contexts/
│   └── Default/                               # Context: Default (non-resource)
│       ├── Procedure/
│       │   ├── Actions/
│       │   │   └── CreateCompletionAction.php
│       │   ├── Commands/
│       │   │   └── CreateCompletionCommand.php
│       │   └── Providers/
│       │       └── OpenAIServiceProvider.php
│       └── Requests/
│           └── CreateChatCompletionRequest.php
└── Shared/
    ├── Auth/
    │   ├── OpenAISdkAuthStrategy.php
    │   └── OpenAISdkClient.php
    ├── Support/
    │   ├── OpenAIConfig.php
    │   └── OpenAIErrorMapper.php
    └── Transfer/
        ├── Procedure/
        │   ├── OpenAIApiProcedureGateway.php
        │   ├── OpenAIApiProcedureAdapter.php
        │   ├── OpenAIApiProcedureConnector.php
        │   └── OpenAIApiDynamicRequest.php
        └── Rest/
            ├── OpenAIApiRestGateway.php
            ├── OpenAIApiRestAdapter.php
            └── OpenAIApiRestConnector.php
```

## Shared Support

```
Support/
├── Commands/
│   └── GenerateCanonicalFromErrorCommand.php
├── Factory/
│   └── CanonicalFactory.php                   # Moved from Support/
├── InputSource/
│   ├── ArraySource.php
│   ├── ConsoleSource.php
│   ├── InputSource.php
│   ├── ModelSource.php
│   └── RequestSource.php
├── Logging/
│   ├── LoggingService.php
│   ├── LoggingServiceProvider.php
│   └── Redactor.php
├── Operation/
│   ├── GatewayPreflightTrait.php
│   ├── OperationDescription.php
│   └── ValidationException.php
├── Validation/
│   └── InputSpecValidator.php                 # Moved from Support/
├── Page.php
└── Result.php
```

## Policies

```
Pleni/Policies/
├── LoggingPolicy.php
├── MetricsPolicy.php
├── RateLimitPolicy.php
└── RetryBackoffPolicy.php
```

## Key Patterns

### CRUD Adapter Pattern (SDK or REST)

**SDK-based** (Google Ads):
```
Context/{Resource}/
├── Adapter/{Resource}CrudAdapter.php          # Uses SDK
├── Gateway/{Resource}CrudGateway.php          # Stable facade
└── DTO/{Resource}CanonicalDTO.php
```

**REST-based** (Stripe):
```
Context/{Resource}/
├── Adapter/{Resource}CrudAdapter.php          # Uses REST via Saloon
├── (Gateway at Shared/Transfer/Rest level)
└── DTO/{Resource}CanonicalDTO.php
```

### Procedure Pattern (RPC/One-off Operations)

```
Context/Default/
├── Procedure/
│   ├── Actions/{Operation}Action.php
│   ├── Commands/{Operation}Command.php
│   └── Providers/ServiceProvider.php
└── Requests/{Operation}Request.php
```

### Shared Transfer Layer

Every provider has:
```
Shared/Transfer/
├── Procedure/                                 # For RPC-style calls
│   ├── {Provider}{Domain}ProcedureGateway.php
│   ├── {Provider}{Domain}ProcedureAdapter.php
│   └── {Provider}{Domain}ProcedureConnector.php (Saloon)
└── Rest/                                      # For RESTful calls
    ├── {Provider}{Domain}RestGateway.php
    ├── {Provider}{Domain}RestAdapter.php
    └── {Provider}{Domain}RestConnector.php (Saloon)
```

## Naming Conventions

| Pattern | Example |
|---------|---------|
| CRUD Adapter | `{Resource}CrudAdapter.php` |
| CRUD Gateway | `{Resource}CrudGateway.php` |
| Procedure Gateway | `{Provider}{Domain}ProcedureGateway.php` |
| Procedure Adapter | `{Provider}{Domain}ProcedureAdapter.php` |
| Rest Gateway | `{Provider}{Domain}RestGateway.php` |
| Rest Adapter | `{Provider}{Domain}RestAdapter.php` |
| Action | `{Operation}Action.php` |
| Command | `{Operation}Command.php` |
| Canonical DTO | `{Resource}CanonicalDTO.php` |
| Selector | `{Resource}Selector.php` |

## File Organization Rules

1. **Requests** folder sits alongside Operations/Actions/Commands at the Context level
2. **Support** folders contain:
   - `Factories/` - Factory classes
   - `Validators/` - Validation classes
   - Other context-specific helpers
3. **Adapter** folder contains provider-specific implementation details
4. **Gateway** provides stable, predictable facade
5. **Shared/Transfer** contains communication layer (Procedure/Rest)

## Notes

- **Context**: Groups resources/operations that share business rules (e.g., Search campaigns vs Display campaigns)
- **Default Context**: Used for non-resource operations or when no specific context applies
- **CRUD vs Procedure**: CRUD for resource management (Create, Read, Update, Delete), Procedure for one-off operations
- **SDK vs REST**: Both can be used for CRUD; choice depends on provider's primary interface
