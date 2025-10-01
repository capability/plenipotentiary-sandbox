# Plenipotentiary Inventory

## Folder tree

.
├── AGENTS.md
├── composer.json
├── composer.lock
├── config
│   └── pleni.php
├── packages
├── pest.php
├── phpunit.xml
├── pleni-inventory.md
├── scripts
│   └── export-src-inventory.sh
├── src
│   ├── Auth
│   │   ├── HmacAuthStrategy.php
│   │   ├── NoopAuthStrategy.php
│   │   ├── OAuth2ClientCredentialsStrategy.php
│   │   ├── TokenAuthStrategy.php
│   │   └── TokenStore
│   │       └── InMemoryTokenStore.php
│   ├── Contracts
│   │   ├── Adapter
│   │   │   ├── AdapterVerbContract.php
│   │   │   ├── ApiCrudAdapterContract.php
│   │   │   └── ApiRpcAdapterContract.php
│   │   ├── Auth
│   │   │   ├── AuthStrategyContract.php
│   │   │   └── SdkAuthStrategyContract.php
│   │   ├── Client
│   │   │   ├── HttpProviderClientContract.php
│   │   │   └── ProviderClientContract.php
│   │   ├── DTO
│   │   │   └── CanonicalDTOContract.php
│   │   ├── Error
│   │   │   └── ErrorMapperContract.php
│   │   ├── Gateway
│   │   │   ├── ApiCrudGatewayContract.php
│   │   │   └── ApiRpcGatewayContract.php
│   │   ├── Idempotency
│   │   │   ├── EndpointIdempotencyHints.php
│   │   │   ├── IdempotencyHints.php
│   │   │   └── IdempotencyStore.php
│   │   ├── Repository
│   │   │   └── BaseRepositoryContract.php
│   │   ├── Selector
│   │   │   └── SelectorContract.php
│   │   └── Token
│   │       └── TokenStoreContract.php
│   ├── Exceptions
│   │   ├── AuthException.php
│   │   ├── DomainException.php
│   │   ├── DomainInvalidException.php
│   │   ├── PermissionException.php
│   │   ├── TransportException.php
│   │   └── ValidationException.php
│   ├── Idempotency
│   │   └── CacheIdempotencyStore.php
│   ├── Pleni
│   │   ├── eBay
│   │   │   └── Browse
│   │   │       ├── Contexts
│   │   │       │   └── Default
│   │   │       │       └── RpcConnector
│   │   │       │           ├── Actions
│   │   │       │           │   └── SearchItemsAction.php
│   │   │       │           ├── Adapter
│   │   │       │           │   └── eBayBrowseApiRpcAdapter.php
│   │   │       │           ├── Commands
│   │   │       │           │   └── SearchItemsCommand.php
│   │   │       │           ├── Gateway
│   │   │       │           │   └── eBayBrowseApiRpcGateway.php
│   │   │       │           └── Providers
│   │   │       │               └── eBayBrowseServiceProvider.php
│   │   │       └── Shared
│   │   │           ├── Auth
│   │   │           │   ├── eBaySdkAuthStrategy.php
│   │   │           │   └── eBaySdkClient.php
│   │   │           └── Support
│   │   │               └── eBayErrorMapper.php
│   │   ├── Examples
│   │   │   └── ApiEndpointUsageExample.php
│   │   ├── Google
│   │   │   └── Ads
│   │   │       ├── Contexts
│   │   │       │   └── Search
│   │   │       │       └── Campaign
│   │   │       │           ├── Adapter
│   │   │       │           │   ├── CampaignApiCrudAdapter.php
│   │   │       │           │   ├── CampaignCreate.php
│   │   │       │           │   ├── CampaignDelete.php
│   │   │       │           │   ├── CampaignRead.php
│   │   │       │           │   ├── CampaignReadMany.php
│   │   │       │           │   ├── CampaignUpdate.php
│   │   │       │           │   └── CreateSupport
│   │   │       │           │       └── CampaignCreateBudget.php
│   │   │       │           ├── DTO
│   │   │       │           │   └── CampaignCanonicalDTO.php
│   │   │       │           ├── Gateway
│   │   │       │           │   └── CampaignApiCrudGateway.php
│   │   │       │           ├── Providers
│   │   │       │           │   └── CampaignServiceProvider.php
│   │   │       │           ├── Repository
│   │   │       │           │   ├── CampaignRepositoryContract.php
│   │   │       │           │   ├── EloquentCampaignRepository.php
│   │   │       │           │   ├── InMemoryCampaignRepository.php
│   │   │       │           │   └── MongoCampaignRepository.php
│   │   │       │           ├── Selector
│   │   │       │           │   └── CampaignSelector.php
│   │   │       │           └── Support
│   │   │       │               └── CampaignIdempotencyHints.php
│   │   │       └── Shared
│   │   │           ├── Auth
│   │   │           │   ├── GoogleAdsSdkAuthStrategy.php
│   │   │           │   └── GoogleAdsSdkClient.php
│   │   │           ├── Lookup
│   │   │           │   ├── Criterion.php
│   │   │           │   ├── Dir.php
│   │   │           │   ├── Gaql
│   │   │           │   │   └── QueryBuilder.php
│   │   │           │   ├── Lookup.php
│   │   │           │   ├── Op.php
│   │   │           │   ├── Page.php
│   │   │           │   └── Sort.php
│   │   │           ├── Providers
│   │   │           │   └── GoogleAdsServiceProvider.php
│   │   │           └── Support
│   │   │               ├── GoogleAdsDefaults.php
│   │   │               ├── GoogleAdsErrorMapper.php
│   │   │               └── GoogleAdsHelper.php
│   │   └── OpenAI
│   │       ├── Contexts
│   │       │   └── Default
│   │       │       └── RpcConnector
│   │       │           ├── Actions
│   │       │           │   └── CreateCompletionAction.php
│   │       │           ├── Adapter
│   │       │           │   └── OpenAIAdapter.php
│   │       │           ├── Commands
│   │       │           │   └── CreateCompletionCommand.php
│   │       │           ├── Gateway
│   │       │           │   └── OpenAIGateway.php
│   │       │           └── Providers
│   │       │               └── OpenAIServiceProvider.php
│   │       └── Shared
│   │           ├── Auth
│   │           │   ├── OpenAISdkAuthStrategy.php
│   │           │   └── OpenAISdkClient.php
│   │           └── Support
│   │               └── OpenAIErrorMapper.php
│   ├── Providers
│   │   └── PleniCoreServiceProvider.php
│   └── Support
│       ├── CanonicalFactory.php
│       ├── Commands
│       │   └── GenerateCanonicalFromErrorCommand.php
│       ├── InputSource
│       │   ├── ArraySource.php
│       │   ├── ConsoleSource.php
│       │   ├── InputSource.php
│       │   ├── ModelSource.php
│       │   └── RequestSource.php
│       ├── InputSpecValidator.php
│       ├── Logging
│       │   ├── LoggingService.php
│       │   ├── LoggingServiceProvider.php
│       │   └── Redactor.php
│       ├── Operation
│       │   ├── GatewayPreflightTrait.php
│       │   ├── OperationDescription.php
│       │   └── ValidationException.php
│       ├── Page.php
│       └── Result.php
└── tests
    ├── Contracts
    │   ├── ApiContractTestCase.php
    │   ├── CustomersContractTest.php
    │   └── ServiceProviderContractTest.php
    ├── Feature
    │   └── ExampleTest.php
    ├── Package
    │   ├── ConfigurationTest.php
    │   ├── ExampleTest.php
    │   └── LoadsConfigTest.php
    ├── Pest.php
    ├── Stubs
    │   ├── Auth
    │   │   └── FakeGoogleAdsSdkAuthStrategy.php
    │   ├── Idempotency
    │   │   └── FakeCampaignIdempotencyHints.php
    │   └── Models
    │       ├── Ad.php
    │       ├── AdGroup.php
    │       └── Campaign.php
    ├── Support
    │   ├── ResultTest.php
    │   ├── TestCase.php
    │   └── TestHelpers.php
    ├── TestCase.php
    └── Unit
        ├── Adapter
        │   ├── CampaignCreateTest.php
        │   ├── CampaignDeleteTest.php
        │   ├── CampaignReadTest.php
        │   ├── CampaignUpdateTest.php
        │   ├── eBayBrowseAdapterTest.php
        │   └── OpenAIAdapterTest.php
        ├── Console
        │   └── GenerateCanonicalFromErrorCommandTest.php
        ├── Contracts
        ├── DTO
        │   └── CampaignCanonicalDTOTest.php
        ├── ExampleTest.php
        ├── Gateway
        │   └── CampaignApiCrudGatewayTest.php
        ├── Idempotency
        │   └── CacheIdempotencyStoreTest.php
        ├── Selector
        │   └── CampaignSelectorTest.php
        └── Support
            ├── CanonicalFactoryTest.php
            └── GoogleAdsErrorMapperTest.php

90 directories, 131 files

## PHP classes & methods


Generated on: 2025-10-01T11:56:11+01:00
