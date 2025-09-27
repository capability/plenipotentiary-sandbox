---

## 5) Providers

### Core
**Providers/PleniCoreServiceProvider.php**
- Registers only baseline bindings. Provider-agnostic.

### Provider-specific
**Pleni/Google/Ads/Shared/Providers/GoogleAdsServiceProvider.php**
- Wires up:
  - `SdkAuthStrategyContract` → `GoogleAdsSdkAuthStrategy`
  - `SdkClientContract` (wrapper around GoogleAdsClient)
  - Campaign mappers
  - Campaign adapter
  - Campaign gateway
  - Error mapper

---

## 6) Gateway + Adapter Interaction

- Gateways are **provider-agnostic entry points**.  
- They delegate actual CRUD methods (create/read/update/delete/list) to the relevant **Adapter**.  
- Adapters are provider-specific and know how to call the underlying SDK.  

E.g. `CampaignApiCrudGateway::create()` → delegates → `CampaignApiCrudAdapter::create()` → uses `GoogleAdsSdkClient`.

---

## 7) Error Handling

- Standardized via `ErrorMapperContract`.  
- Provider ServiceProviders bind official error mappers.  
- Userland can override error mappers in `pleni.php`.

---

## 8) Tests

- Will target **contracts** and verify providers/adapters resolve correctly.  
- Fake `SdkClientContract` can be injected for provider tests without hitting external APIs.

---

✅ This reflects the **current structure & conventions**:
- Pluralized `Providers/`, 
- Gateway+Adapter split, 
- DTO + Mapper layering, 
- Auth strategies abstracted.

# technical-specs.md — Plenipotentiary-Laravel (current structure)

> **Purpose**: Define the current file structure, contracts, gateways, adapters, mappers, auth strategies, error mappers, and service providers. This reflects the *implemented* code layout, not just the original charter draft.

---

## 1) Folder Structure

```
packages/plenipotentiary-laravel/
  config/pleni.php
  src/
    Providers/
      PleniCoreServiceProvider.php
    Contracts/
      Auth/AuthStrategyContract.php
      Auth/SdkAuthStrategyContract.php
      DTO/InboundDTOContract.php
      DTO/OutboundDTOContract.php
      Gateway/ApiCrudGatewayContract.php
      Adapter/ApiCrudAdapter.php
      Mapper/InboundMapperContract.php
      Mapper/OutboundMapperContract.php
      Error/ErrorMapperContract.php
      Token/TokenStoreContract.php
    Auth/
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
          CampaignApiCrudAdapter.php
        Gateway/
          CampaignApiCrudGateway.php
      Google/Ads/Shared/
        Auth/GoogleAdsSdkAuthStrategy.php
        Providers/GoogleAdsServiceProvider.php
        Support/GoogleAdsHelper.php
        Support/GoogleAdsErrorMapper.php
  tests/
    // TBD — core contract tests + provider fake clients
```

---

## 2) Config (driver maps and overrides)

**config/pleni.php**

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    | Enable/disable providers.
    */
    'providers' => [
        'google-ads' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Adapter overrides
    |--------------------------------------------------------------------------
    | By default the ServiceProviders bind official adapters.
    | You may override them with a custom adapter.
    */
    'adapters' => [
        'campaign' => \App\Adapters\MyCustomCampaignAdapter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Mapper Overrides
    |--------------------------------------------------------------------------
    */
    'error_mappers' => [
        'default' => \App\Mappers\MyErrorMapper::class,
    ],
];
```

---

## 3) Contracts

**Contracts/Auth/AuthStrategyContract.php**

```php
namespace Plenipotentiary\Laravel\Contracts\Auth;

use Psr\Http\Message\RequestInterface;

interface AuthStrategyContract
{
    public function apply(RequestInterface $request, array $context = []): RequestInterface;
}
```

**Contracts/Gateway/ApiCrudGatewayContract.php**

```php
namespace Plenipotentiary\Laravel\Contracts\Gateway;

use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;

interface ApiCrudGatewayContract
{
    public function create(OutboundDTOContract $dto): InboundDTOContract;
    public function read(int|string $id): ?InboundDTOContract;
    public function update(OutboundDTOContract $dto): InboundDTOContract;
    public function delete(int|string $id): bool;
    public function listAll(array $criteria = []): iterable;
}
```

**Contracts/Adapter/ApiCrudAdapter.php**

```php
namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;

interface ApiCrudAdapter
{
    public function create(OutboundDTOContract $dto): InboundDTOContract;
    public function read(string|int $id): ?InboundDTOContract;
    public function update(OutboundDTOContract $dto): InboundDTOContract;
    public function delete(string|int $id): bool;
    public function listAll(array $criteria = []): iterable;
}
```

**Contracts/DTO/InboundDTOContract.php**

```php
namespace Plenipotentiary\Laravel\Contracts\DTO;

interface InboundDTOContract
{
    public function toArray(): array;
    public static function fromArray(array $data): self;
}
```

**Contracts/DTO/OutboundDTOContract.php**

```php
namespace Plenipotentiary\Laravel\Contracts\DTO;

interface OutboundDTOContract
{
    public function toArray(): array;
    public static function fromArray(array $data): self;
}
```

**Contracts/Mapper/InboundMapperContract.php**

```php
namespace Plenipotentiary\Laravel\Contracts\Mapper;

use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;

interface InboundMapperContract
{
    public function map(array $payload): InboundDTOContract;
}
```

**Contracts/Mapper/OutboundMapperContract.php**

```php
namespace Plenipotentiary\Laravel\Contracts\Mapper;

use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;

interface OutboundMapperContract
{
    public function map(OutboundDTOContract $dto): array;
}
```

**Contracts/Error/ErrorMapperContract.php**

```php
namespace Plenipotentiary\Laravel\Contracts\Error;

interface ErrorMapperContract
{
    public function map(\Throwable $e): \Throwable;
}
```

---

## 4) DTOs

**CampaignOutboundDTO.php**

```php
namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use Plenipotentiary\Laravel\Contracts\OutboundDTOContract;

readonly class CampaignOutboundDTO implements OutboundDTOContract
{
    public function __construct(
        public string $name,
        public int $budgetMicros,
        public string $advertisingChannelType,
    ) {}
}
```

**CampaignInboundDTO.php**

```php
namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use Plenipotentiary\Laravel\Contracts\InboundDTOContract;

readonly class CampaignInboundDTO implements InboundDTOContract
{
    public function __construct(
        public string $id,
        public string $resourceName,
        public string $status,
    ) {}
}
```

---

## 5) Mappers

**CampaignOutboundMapper.php**

```php
namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Mapper;

use Plenipotentiary\Laravel\Contracts\OutboundMapperContract;
use Plenipotentiary\Laravel\Contracts\OutboundDTOContract;

class CampaignOutboundMapper implements OutboundMapperContract
{
    public function map(OutboundDTOContract $dto): array
    {
        return [
            'name' => $dto->name,
            'campaignBudget' => $dto->budgetMicros,
            'advertisingChannelType' => $dto->advertisingChannelType,
        ];
    }
}
```

**CampaignInboundMapper.php**

```php
namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Mapper;

use Plenipotentiary\Laravel\Contracts\InboundMapperContract;
use Plenipotentiary\Laravel\Contracts\InboundDTOContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignInboundDTO;

class CampaignInboundMapper implements InboundMapperContract
{
    public function map(array $payload): InboundDTOContract
    {
        return new CampaignInboundDTO(
            id: $payload['id'],
            resourceName: $payload['resourceName'],
            status: $payload['status'],
        );
    }
}
```

---

## 6) Adapter

**GoogleAdsCampaignAdapter.php**

```php
namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Plenipotentiary\Laravel\Contracts\CampaignPort;
use Plenipotentiary\Laravel\Contracts\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\InboundDTOContract;
use Plenipotentiary\Laravel\Contracts\OutboundMapperContract;
use Plenipotentiary\Laravel\Contracts\InboundMapperContract;
use Plenipotentiary\Laravel\Contracts\ErrorMapperContract;

class GoogleAdsCampaignAdapter implements CampaignPort
{
    public function __construct(
        private \Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient $client,
        private OutboundMapperContract $outboundMapper,
        private InboundMapperContract $inboundMapper,
        private ErrorMapperContract $errorMapper,
    ) {}

    public function create(OutboundDTOContract $dto): InboundDTOContract
    {
        try {
            $payload = $this->outboundMapper->map($dto);

            $response = $this->client->getCampaignServiceClient()->mutateCampaigns(
                $this->client->getLoginCustomerId(),
                [$payload]
            );

            $resource = $response->getResults()[0];
            return $this->inboundMapper->map([
                'id' => $resource->getId(),
                'resourceName' => $resource->getResourceName(),
                'status' => $resource->getStatus(),
            ]);
        } catch (\Throwable $e) {
            throw $this->errorMapper->map($e);
        }
    }
}
```

---

## 7) Error Mappers

**Errors/DefaultErrorMapper.php**

```php
namespace Plenipotentiary\Laravel\Errors;

use Plenipotentiary\Laravel\Contracts\ErrorMapperContract;

class DefaultErrorMapper implements ErrorMapperContract
{
    public function map(\Throwable $e): \Throwable
    {
        // Map generic HTTP / network exceptions to Transport/Auth/Validation
        return $e;
    }
}
```

**Errors/ChainErrorMapper.php**

```php
namespace Plenipotentiary\Laravel\Errors;

use Plenipotentiary\Laravel\Contracts\ErrorMapperContract;

class ChainErrorMapper implements ErrorMapperContract
{
    /** @var ErrorMapperContract[] */
    private array $mappers;

    public function __construct(array $mappers)
    {
        $this->mappers = $mappers;
    }

    public function map(\Throwable $e): \Throwable
    {
        foreach ($this->mappers as $mapper) {
            $e = $mapper->map($e);
        }
        return $e;
    }
}
```

**Pleni/Google/Shared/Errors/GoogleAdsErrorMapper.php**

```php
namespace Plenipotentiary\Laravel\Pleni\Google\Shared\Errors;

use Plenipotentiary\Laravel\Contracts\ErrorMapperContract;

class GoogleAdsErrorMapper implements ErrorMapperContract
{
    public function map(\Throwable $e): \Throwable
    {
        // Inspect GoogleAdsException and translate to Auth/Domain/Validation exceptions
        return $e;
    }
}
```

**Pleni/Google/Ads/Contexts/Search/Campaign/Errors/CampaignErrorMapper.php**

```php
namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Errors;

use Plenipotentiary\Laravel\Contracts\ErrorMapperContract;

class CampaignErrorMapper implements ErrorMapperContract
{
    public function map(\Throwable $e): \Throwable
    {
        // Optional campaign-specific mappings
        return $e;
    }
}
```

---

## 8) Service Provider Binding

**PleniServiceProvider.php**

```php
namespace Plenipotentiary\Laravel;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\CampaignPort;
use Plenipotentiary\Laravel\Contracts\ErrorMapperContract;
use Plenipotentiary\Laravel\Errors\ChainErrorMapper;

class PleniServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Adapter binding
        $cfg    = config('pleni.adapters.google.ads.search.campaign', []);
        $driver = data_get($cfg, 'driver', 'local');
        $class  = data_get($cfg, "map.$driver");

        if (!is_string($class) || !class_exists($class)) {
            throw new \RuntimeException("Adapter for driver [$driver] not found.");
        }
        if (!is_subclass_of($class, CampaignPort::class)) {
            throw new \RuntimeException("[$class] must implement CampaignPort.");
        }

        $this->app->bind(CampaignPort::class, fn ($app) => $app->make($class));

        // Error mapper binding
        $cfg    = config('pleni.errors.google.ads.search.campaign', []);
        $driver = data_get($cfg, 'driver', 'default');
        $classes = data_get($cfg, "map.$driver", []);

        $this->app->bind(ErrorMapperContract::class, function ($app) use ($classes) {
            $mappers = array_map(fn($c) => $app->make($c), $classes);
            return new ChainErrorMapper($mappers);
        });
    }
}
```

---

## 9) Tests

**tests/Contract/CampaignPortTest.php**

```php
it('can create a campaign via CampaignPort contract', function () {
    $dto = new \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignOutboundDTO(
        name: 'Test Campaign',
        budgetMicros: 1000000,
        advertisingChannelType: 'SEARCH'
    );

    $port = app(\Plenipotentiary\Laravel\Contracts\CampaignPort::class);

    $result = $port->create($dto);

    expect($result)->toBeInstanceOf(\Plenipotentiary\Laravel\Contracts\InboundDTOContract::class);
});
```

**tests/Adapter/GoogleAdsCampaignAdapterTest.php**

```php
it('resolves to the GoogleAdsCampaignAdapter when driver=local', function () {
    config()->set('pleni.adapters.google.ads.search.campaign.driver', 'local');

    $port = app(\Plenipotentiary\Laravel\Contracts\CampaignPort::class);

    expect($port)->toBeInstanceOf(
        \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\GoogleAdsCampaignAdapter::class
    );
});
```

---

