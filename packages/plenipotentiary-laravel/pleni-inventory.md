# Plenipotentiary Inventory

## Folder tree

.
├── AGENTS.md
├── composer.json
├── composer.lock
├── config
│   └── pleni.php
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
│   │   │   ├── ApiCrudAdapterContract.php
│   │   │   └── SpecContract.php
│   │   ├── Auth
│   │   │   ├── AuthStrategyContract.php
│   │   │   └── SdkAuthStrategyContract.php
│   │   ├── Client
│   │   │   └── ProviderClientContract.php
│   │   ├── DTO
│   │   ├── Error
│   │   │   └── ErrorMapperContract.php
│   │   ├── Gateway
│   │   │   └── ApiCrudGatewayContract.php
│   │   ├── Idempotency
│   │   │   ├── IdempotencyHints.php
│   │   │   └── IdempotencyStore.php
│   │   ├── Mapper
│   │   │   └── OutboundMapperContract.php
│   │   ├── Repository
│   │   │   └── BaseRepositoryContract.php
│   │   └── Token
│   │       └── TokenStoreContract.php
│   ├── Idempotency
│   │   └── CacheIdempotencyStore.php
│   ├── Pleni
│   │   └── Google
│   │       └── Ads
│   │           ├── Contexts
│   │           │   └── Search
│   │           │       └── Campaign
│   │           │           ├── Adapter
│   │           │           │   ├── AdapterSupport
│   │           │           │   ├── CampaignApiCrudAdapter.php
│   │           │           │   ├── Create
│   │           │           │   │   ├── Budget
│   │           │           │   │   │   └── RequestMapper.php
│   │           │           │   │   ├── CreateRequestMapperContract.php
│   │           │           │   │   ├── CreateResponseMapperContract.php
│   │           │           │   │   ├── RequestMapper.php
│   │           │           │   │   ├── ResponseMapper.php
│   │           │           │   │   └── Spec.php
│   │           │           │   ├── Delete
│   │           │           │   │   ├── DeleteRequestMapperContract.php
│   │           │           │   │   ├── DeleteResponseMapperContract.php
│   │           │           │   │   ├── RequestMapper.php
│   │           │           │   │   ├── ResponseMapper.php
│   │           │           │   │   └── Spec.php
│   │           │           │   ├── Read
│   │           │           │   │   ├── LookupRequestMapper.php
│   │           │           │   │   ├── LookupResponseMapper.php
│   │           │           │   │   └── ReadRequestMapperContract.php
│   │           │           │   └── Update
│   │           │           │       ├── RequestMapper.php
│   │           │           │       ├── ResponseMapper.php
│   │           │           │       ├── Spec.php
│   │           │           │       ├── UpdateRequestMapperContract.php
│   │           │           │       └── UpdateResponseMapperContract.php
│   │           │           ├── DTO
│   │           │           │   └── CampaignCanonicalDTO.php
│   │           │           ├── Gateway
│   │           │           │   └── CampaignApiCrudGateway.php
│   │           │           ├── Key
│   │           │           │   ├── CampaignSelector.php
│   │           │           │   └── CampaignSelectorKind.php
│   │           │           ├── Mapper
│   │           │           └── Repository
│   │           │               ├── CampaignRepositoryContract.php
│   │           │               └── EloquentCampaignRepository.php
│   │           └── Shared
│   │               ├── Auth
│   │               │   ├── GoogleAdsSdkAuthStrategy.php
│   │               │   └── GoogleAdsSdkClient.php
│   │               ├── Lookup
│   │               │   ├── Criterion.php
│   │               │   ├── Dir.php
│   │               │   ├── Gaql
│   │               │   │   └── QueryBuilder.php
│   │               │   ├── Lookup.php
│   │               │   ├── Op.php
│   │               │   ├── Page.php
│   │               │   └── Sort.php
│   │               ├── Providers
│   │               │   └── GoogleAdsServiceProvider.php
│   │               └── Support
│   │                   ├── GoogleAdsErrorMapper.php
│   │                   └── GoogleAdsHelper.php
│   ├── Providers
│   │   └── PleniCoreServiceProvider.php
│   ├── Support
│   │   ├── Logging
│   │   │   ├── LoggingService.php
│   │   │   ├── LoggingServiceProvider.php
│   │   │   └── Redactor.php
│   │   ├── Operation
│   │   │   ├── OperationDescription.php
│   │   │   └── ValidationException.php
│   │   ├── Page.php
│   │   └── Result.php
│   └── Traits
│       └── HandlesEloquentCrud.php
└── tests
    ├── Contracts
    │   ├── ApiContractTestCase.php
    │   └── CustomersContractTest.php
    ├── Feature
    │   ├── CampaignApiCrudAdapterTest.php
    │   └── ExampleTest.php
    ├── Package
    │   ├── BindsContractsTest.php
    │   ├── ExampleTest.php
    │   └── LoadsConfigTest.php
    ├── Pest.php
    ├── Support
    │   └── TestCase.php
    ├── TestCase.php
    └── Unit
        ├── Contracts
        ├── ExampleTest.php
        ├── Support
        │   ├── GeneratedServiceHelpersTest.php
        │   ├── GoogleAdsUtilTest.php
        │   └── ValidatorsTest.php
        └── Translate
            └── CampaignExternalToDomainMapperTest.php

56 directories, 88 files

## PHP classes & methods


Generated on: 2025-09-26T10:47:29+01:00
