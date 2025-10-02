# eBay Browse API - Architecture Diagram

## Request Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                         DEVELOPER CODE                               │
│  (Controllers, Commands, Jobs, Event Listeners, etc.)               │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                               │ Injects Action via DI
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        ACTION LAYER                                  │
│  SearchItemsAction::execute(string $query, array $options)          │
│  - Builds Saloon Request object                                     │
│  - Calls Gateway                                                     │
│  - Provider-agnostic                                                │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                               │ Creates Request
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      REQUEST OBJECT                                  │
│  SearchItemsRequest(query: 'laptop', limit: 50)                     │
│  - Type-safe constructor parameters                                 │
│  - Defines endpoint, method, query params                           │
│  - eBay-specific knowledge encapsulated here                        │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                               │ Passes to Gateway
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      GATEWAY LAYER                                   │
│  eBayBrowseRestGateway::execute(Request $request)                   │
│  - Implements RestGatewayContract                                   │
│  - Applies policy chain:                                            │
│    • Logging                                                        │
│    • Idempotency                                                    │
│    • Rate limiting                                                  │
│    • Retry logic                                                    │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                               │ Delegates to Adapter
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      ADAPTER LAYER                                   │
│  eBayBrowseRestAdapter::execute(Request $request)                   │
│  - Implements RestAdapterContract                                   │
│  - Uses Saloon Connector                                            │
│  - Maps HTTP errors to domain errors                                │
│  - Returns Result object                                            │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                               │ Uses Connector
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                   SALOON CONNECTOR                                   │
│  eBayBrowseRestConnector (extends Saloon\Http\Connector)           │
│  - Resolves base URL (production/sandbox)                           │
│  - Adds authentication headers                                      │
│  - Sets timeouts                                                    │
│  - Sends HTTP request                                               │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
                               │ HTTP Request
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    EBAY BROWSE API                                   │
│  https://api.ebay.com/buy/browse/v1/item_summary/search            │
└─────────────────────────────────────────────────────────────────────┘
```

## Component Relationships

```
┌──────────────────────────────────────────────────────────────┐
│                    YOUR APPLICATION                           │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌───────────────┐  ┌───────────────┐  ┌───────────────┐   │
│  │  Controller   │  │   Command     │  │     Job       │   │
│  └───────┬───────┘  └───────┬───────┘  └───────┬───────┘   │
│          │                   │                   │           │
│          └───────────────────┼───────────────────┘           │
│                              │                               │
│                      ┌───────▼──────┐                        │
│                      │    Action    │                        │
│                      └───────┬──────┘                        │
│                              │                               │
└──────────────────────────────┼───────────────────────────────┘
                               │
┌──────────────────────────────┼───────────────────────────────┐
│      DOMAIN LAYER            │                               │
│                      ┌───────▼──────┐                        │
│                      │  Saloon      │                        │
│                      │  Request     │                        │
│                      └───────┬──────┘                        │
│                              │                               │
└──────────────────────────────┼───────────────────────────────┘
                               │
┌──────────────────────────────┼───────────────────────────────┐
│      GATEWAY LAYER           │                               │
│                      ┌───────▼──────────────┐                │
│                      │  RestGateway         │                │
│                      │  (Interface)         │                │
│                      └───────┬──────────────┘                │
│                              │                               │
│                      ┌───────▼──────────────┐                │
│                      │ eBayBrowseRestGateway│                │
│                      │ (Implementation)     │                │
│                      └───────┬──────────────┘                │
│                              │                               │
└──────────────────────────────┼───────────────────────────────┘
                               │
┌──────────────────────────────┼───────────────────────────────┐
│      ADAPTER LAYER           │                               │
│                      ┌───────▼──────────────┐                │
│                      │  RestAdapter         │                │
│                      │  (Interface)         │                │
│                      └───────┬──────────────┘                │
│                              │                               │
│                      ┌───────▼──────────────┐                │
│                      │ eBayBrowseRestAdapter│                │
│                      │                      │                │
│                      └───────┬──────────────┘                │
│                              │                               │
└──────────────────────────────┼───────────────────────────────┘
                               │
┌──────────────────────────────┼───────────────────────────────┐
│   INFRASTRUCTURE LAYER       │                               │
│                      ┌───────▼───────────────┐               │
│                      │ eBayBrowseRestConnector│              │
│                      │ (Saloon Connector)    │               │
│                      └───────┬───────────────┘               │
│                              │                               │
│                      ┌───────▼───────────┐                   │
│                      │  HTTP Client      │                   │
│                      │  (Guzzle)         │                   │
│                      └───────────────────┘                   │
│                              │                               │
└──────────────────────────────┼───────────────────────────────┘
                               │
                               ▼
                        eBay Browse API
```

## Dependency Flow (What Depends on What)

```
Application Layer
    ↓ depends on
Action Layer
    ↓ depends on
Gateway Interface (RestGatewayContract)
    ↑ implemented by
Gateway Implementation (eBayBrowseRestGateway)
    ↓ depends on
Adapter Interface (RestAdapterContract)
    ↑ implemented by
Adapter Implementation (eBayBrowseRestAdapter)
    ↓ depends on
Saloon Connector (eBayBrowseRestConnector)
    ↓ uses
HTTP Client (Guzzle/Saloon)
```

## File Organization

```
Pleni/eBay/Browse/
│
├── Contexts/Default/           ← Application & Domain Layer
│   ├── Actions/                ← Business logic (domain)
│   ├── Commands/               ← CLI (application)
│   ├── Controllers/            ← HTTP (application)
│   ├── Jobs/                   ← Background (application)
│   ├── Requests/               ← API Endpoint Definitions (eBay-specific)
│   └── Providers/              ← Service Provider (wiring)
│
└── Shared/                     ← Infrastructure & Support
    ├── Auth/                   ← Authentication strategies
    ├── Support/                ← Config & error mapping
    └── Transfer/
        ├── Procedure/          ← Alternative RPC pattern
        └── Rest/               ← REST/Saloon pattern
            ├── Gateway         ← Stable interface
            ├── Adapter         ← eBay communication
            └── Connector       ← HTTP client config
```

## Pattern Benefits Visualized

### Traditional Approach (❌ Tight Coupling)

```
┌────────────────────────┐
│   Your Controller      │
│                        │
│ use eBay\SDK\Browse;   │  ← Direct dependency on eBay SDK
│                        │
│ $client = new Browse();│  ← Tightly coupled
│ $result = $client->    │
│   search([...]);       │
└────────────────────────┘
```

**Problems:**
- Hard to test (need real eBay credentials)
- Hard to swap providers
- No centralized logging/retry
- Domain knows about eBay

### Plenipotentiary Approach (✅ Loose Coupling)

```
┌────────────────────────────────────────────┐
│   Your Controller                          │
│                                            │
│ use SearchItemsAction;                     │  ← Depends on domain
│                                            │
│ public function __construct(               │
│     private SearchItemsAction $action      │  ← Injected
│ ) {}                                       │
│                                            │
│ $result = $this->action->execute('laptop');│  ← Provider-agnostic
└────────────────────────────────────────────┘
         │
         │ (abstracted through multiple layers)
         ▼
┌────────────────────────────────────────────┐
│      eBay Browse API                       │
│   (Hidden behind Gateway/Adapter)          │
└────────────────────────────────────────────┘
```

**Benefits:**
- ✅ Easy to test (mock action or gateway)
- ✅ Easy to swap providers (change adapter)
- ✅ Centralized logging/retry (in gateway)
- ✅ Domain is provider-agnostic

## Error Handling Flow

```
eBay API Error
    │
    ↓
HTTP Exception (Saloon/Guzzle)
    │
    ↓
eBayErrorMapper::map()
    │
    ├─→ Maps HTTP status codes
    ├─→ Parses eBay error response
    ├─→ Determines if retryable
    │
    ↓
MappedError object
    │
    ├─→ code: 'RATE_LIMIT_EXCEEDED'
    ├─→ message: 'Rate limit exceeded'
    ├─→ httpStatus: 429
    └─→ retryable: true
    │
    ↓
Result::err([...])
    │
    ↓
Returned to Action
    │
    ↓
Your Code
    │
    ├─→ if ($result->isErr())
    │       $error = $result->unwrapErr();
    └─→     // Handle error
```

## IoC Container Wiring

```
Service Provider Registration:
    │
    ├─→ EbayConfig::class
    │   └─→ reads from config/services.php
    │
    ├─→ eBayBrowseRestConnector::class
    │   └─→ depends on EbayConfig
    │
    ├─→ eBayBrowseRestAdapter::class
    │   └─→ depends on Connector, ErrorMapper, Logger
    │
    └─→ RestGatewayContract::class (for eBay Actions)
        └─→ resolves to eBayBrowseRestGateway
            └─→ depends on Adapter, Logger

When you type-hint Action:
    │
    └─→ Laravel auto-injects
        └─→ Action constructor requires RestGatewayContract
            └─→ Laravel resolves to eBayBrowseRestGateway
                └─→ Everything is wired automatically!
```

## Adding a New Endpoint

```
1. Create Request Class (1 file)
   └─→ Contexts/Default/Requests/NewFeatureRequest.php

2. (Optional) Create Action (1 file)
   └─→ Contexts/Default/Actions/NewFeatureAction.php

3. Use It!
   └─→ Inject Action into Controller/Command/Job

NO CHANGES NEEDED:
   ✗ Gateway
   ✗ Adapter
   ✗ Connector
   ✗ Service Provider
```

---

This architecture ensures:
- ✅ **Testability**: Easy to mock at any layer
- ✅ **Maintainability**: Clear separation of concerns
- ✅ **Scalability**: Add features without breaking existing code
- ✅ **Portability**: Swap providers without touching domain
- ✅ **Reliability**: Centralized error handling, logging, retries
