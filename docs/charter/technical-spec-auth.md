Here’s the full **technical-spec.md** with the **auth strategy stubs** (Noop, Token, OAuth2 Client Credentials, HMAC), token store contract, config-driven binding, and tiny tests—aligned with your v1 charter.

---

# technical-spec.md — Plenipotentiary-Laravel v1

> Purpose: Define concrete file structure, contracts, adapters, error mappers, **auth strategies**, config and stubs to implement v1. Complements `project-charter.md`.

---

## 1) Folder Structure

```
packages/plenipotentiary-laravel/
  config/pleni.php
  src/
    PleniServiceProvider.php
    Contracts/
      AuthStrategy.php
      TokenStore.php
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
      TokenStore/
        InMemoryTokenStore.php
    Errors/
      Exceptions/
        TransportException.php
        AuthException.php
        DomainException.php
        ValidationException.php
      DefaultErrorMapper.php
      ChainErrorMapper.php
    Pleni/
      Google/Shared/Errors/
        GoogleAdsErrorMapper.php
      Google/Ads/Contexts/Search/Campaign/
        DTO/
          CampaignInboundDTO.php
          CampaignOutboundDTO.php
        Mapper/
          CampaignInboundMapper.php
          CampaignOutboundMapper.php
        Adapter/
          GoogleAdsCampaignAdapter.php
        Errors/
          CampaignErrorMapper.php
        Port/
          CampaignPort.php
  tests/
    Contract/CampaignPortTest.php
    Adapter/GoogleAdsCampaignAdapterTest.php
    Auth/AuthStrategyBindingTest.php
```

---

## 2) Config (drivers for adapters, errors, auth)

**config/pleni.php**

```php
<?php

return [

    // ---------- Adapter selection (swappable drivers) ----------
    'adapters' => [
        'google' => [
            'ads' => [
                'search' => [
                    'campaign' => [
                        'driver' => env('PLENI_GOOGLE_ADS_SEARCH_CAMPAIGN_DRIVER', 'local'),
                        'map' => [
                            'local' => \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\GoogleAdsCampaignAdapter::class,
                            // future:
                            // 'official' => \Pleni\Adapter\GoogleAds\...\GoogleAdsCampaignAdapter::class,
                            // 'community:vendor1' => \Vendor1\PleniGoogleAds\...\GoogleAdsCampaignAdapter::class,
                            // 'custom' => \App\Adapters\Google\Ads\...\MyCampaignAdapter::class,
                        ],
                    ],
                ],
            ],
        ],
    ],

    // ---------- Error mapper selection (composed chains) ----------
    'errors' => [
        'google' => [
            'ads' => [
                'search' => [
                    'campaign' => [
                        'driver' => env('PLENI_ERR_GOOGLE_ADS_SEARCH_CAMPAIGN_DRIVER', 'default'),
                        'map' => [
                            'default' => [
                                \Plenipotentiary\Laravel\Errors\DefaultErrorMapper::class,
                                \Plenipotentiary\Laravel\Pleni\Google\Shared\Errors\GoogleAdsErrorMapper::class,
                                \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Errors\CampaignErrorMapper::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    // ---------- Auth strategy selection ----------
    'auth' => [
        // global default (can be overridden per provider/service/context/resource)
        'driver' => env('PLENI_AUTH_DRIVER', 'noop'),
        'map' => [
            'noop'   => \Plenipotentiary\Laravel\Auth\NoopAuthStrategy::class,
            'token'  => \Plenipotentiary\Laravel\Auth\TokenAuthStrategy::class,
            'oauth2_client_credentials' => \Plenipotentiary\Laravel\Auth\OAuth2ClientCredentialsStrategy::class,
            'hmac'   => \Plenipotentiary\Laravel\Auth\HmacAuthStrategy::class,
        ],
        // default options for strategies
        'options' => [
            'token' => [
                'header' => 'Authorization',
                'prefix' => 'Bearer ',
                'value'  => env('PLENI_AUTH_TOKEN', ''),
            ],
            'oauth2_client_credentials' => [
                'client_id'     => env('PLENI_OAUTH2_CLIENT_ID', ''),
                'client_secret' => env('PLENI_OAUTH2_CLIENT_SECRET', ''),
                'token_url'     => env('PLENI_OAUTH2_TOKEN_URL', ''),
                'scope'         => env('PLENI_OAUTH2_SCOPE', ''), // space-separated
                'audience'      => env('PLENI_OAUTH2_AUDIENCE', null),
                'cache_ttl'     => env('PLENI_OAUTH2_CACHE_TTL', 3300), // seconds
            ],
            'hmac' => [
                'header'     => 'Authorization',
                'algo'       => env('PLENI_HMAC_ALGO', 'sha256'),
                'key_id'     => env('PLENI_HMAC_KEY_ID', ''),
                'secret'     => env('PLENI_HMAC_SECRET', ''),
                'prefix'     => 'HMAC ',
                // canonicalization options (if needed):
                'signed_headers' => ['(request-target)', 'date', 'content-digest'],
            ],
        ],

        // example: provider-specific override (optional)
        'overrides' => [
            // 'google.ads.search.campaign' => [
            //     'driver' => 'oauth2_client_credentials',
            //     'options' => [ 'scope' => 'https://www.googleapis.com/auth/adwords' ]
            // ]
        ],
    ],

];
```

---

## 3) Contracts

**src/Contracts/AuthStrategy.php**

```php
<?php
namespace Plenipotentiary\Laravel\Contracts;

use Psr\Http\Message\RequestInterface;

interface AuthStrategy
{
    /**
     * Apply auth to a PSR-7 request (add headers/query/etc).
     * Implementations should be side-effect free (return a cloned request).
     */
    public function apply(RequestInterface $request, array $context = []): RequestInterface;
}
```

**src/Contracts/TokenStore.php**

```php
<?php
namespace Plenipotentiary\Laravel\Contracts;

interface TokenStore
{
    public function get(string $key): ?string;
    public function put(string $key, string $value, int $ttlSeconds): void;
    public function forget(string $key): void;

    /**
     * Optional observability: when does this token expire?
     */
    public function expiresAt(string $key): ?int;
}
```

**src/Contracts/CampaignPort.php**

```php
<?php
namespace Plenipotentiary\Laravel\Contracts;

interface CampaignPort
{
    public function create(OutboundDTOContract $dto): InboundDTOContract;

    // Extended CRUD/search to encourage consistency across adapters
    public function read(string|int $id): ?InboundDTOContract;
    public function update(OutboundDTOContract $dto): InboundDTOContract;
    public function delete(string|int $id): bool;
    public function listAll(array $criteria = []): iterable;
}
```

**DTO / Mapper / Error contracts** (as previously provided) remain the same.

---

## 4) Auth Strategies

> Note: These strategies target PSR-7 `RequestInterface`. For Laravel HTTP client usage, wrap requests in PSR-7 or adapt headers easily.

**src/Auth/NoopAuthStrategy.php**

```php
<?php
namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\AuthStrategy;
use Psr\Http\Message\RequestInterface;

final class NoopAuthStrategy implements AuthStrategy
{
    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        return $request;
    }
}
```

**src/Auth/TokenAuthStrategy.php**

```php
<?php
namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\AuthStrategy;
use Psr\Http\Message\RequestInterface;

final class TokenAuthStrategy implements AuthStrategy
{
    public function __construct(
        private string $header = 'Authorization',
        private string $prefix = 'Bearer ',
        private string $value  = ''
    ) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $token = $context['token'] ?? $this->value;
        if ($token === '') {
            return $request;
        }
        return $request->withHeader($this->header, $this->prefix.$token);
    }
}
```

**src/Auth/HmacAuthStrategy.php**

```php
<?php
namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\AuthStrategy;
use Psr\Http\Message\RequestInterface;

final class HmacAuthStrategy implements AuthStrategy
{
    public function __construct(
        private string $keyId,
        private string $secret,
        private string $algo = 'sha256',
        private string $header = 'Authorization',
        private string $prefix = 'HMAC ',
        private array $signedHeaders = ['(request-target)', 'date', 'content-digest'],
    ) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $signature = $this->sign($request);
        return $request->withHeader($this->header, $this->prefix.$this->keyId.':'.$signature);
    }

    private function sign(RequestInterface $req): string
    {
        $method = strtolower($req->getMethod());
        $path   = (string) $req->getUri()->withScheme('')->withHost('')->withPort(null);
        $lines  = [];

        foreach ($this->signedHeaders as $h) {
            if ($h === '(request-target)') {
                $lines[] = "(request-target): {$method} {$path}";
            } else {
                $lines[] = strtolower($h).': '.$req->getHeaderLine($h);
            }
        }

        $stringToSign = implode("\n", $lines);
        return base64_encode(hash_hmac($this->algo, $stringToSign, $this->secret, true));
    }
}
```

**src/Auth/OAuth2ClientCredentialsStrategy.php**

```php
<?php
namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\AuthStrategy;
use Plenipotentiary\Laravel\Contracts\TokenStore;
use Psr\Http\Message\RequestInterface;

final class OAuth2ClientCredentialsStrategy implements AuthStrategy
{
    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $tokenUrl,
        private ?string $scope = null,
        private ?string $audience = null,
        private TokenStore $store,
        private int $cacheTtl = 3300, // seconds
        private ?callable $httpClient = null // function(array $form): array{access_token:string,expires_in:int}
    ) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $cacheKey = $this->cacheKey();
        $token = $this->store->get($cacheKey);

        if (!$token) {
            $token = $this->fetchToken();
            $this->store->put($cacheKey, $token, $this->cacheTtl);
        }

        return $request->withHeader('Authorization', 'Bearer '.$token);
    }

    private function cacheKey(): string
    {
        return 'pleni:o2cc:'.md5(json_encode([
            'cid' => $this->clientId,
            'url' => $this->tokenUrl,
            'scope' => $this->scope,
            'aud' => $this->audience,
        ]));
    }

    private function fetchToken(): string
    {
        $payload = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
        if ($this->scope)    $payload['scope'] = $this->scope;
        if ($this->audience) $payload['audience'] = $this->audience;

        $client = $this->httpClient ?? function(array $form) {
            // Minimal, dependency-light POST using stream contexts
            $opts = ['http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($form),
                'timeout' => 10,
            ]];
            $resp = file_get_contents($this->tokenUrl, false, stream_context_create($opts));
            if ($resp === false) {
                throw new \RuntimeException('OAuth2 token request failed');
            }
            /** @var array{access_token?:string,expires_in?:int} $data */
            $data = json_decode($resp, true) ?? [];
            if (!isset($data['access_token'])) {
                throw new \RuntimeException('OAuth2 token response missing access_token');
            }
            return $data;
        };

        $res = $client($payload);
        return (string) $res['access_token'];
    }
}
```

**src/Auth/TokenStore/InMemoryTokenStore.php**

```php
<?php
namespace Plenipotentiary\Laravel\Auth\TokenStore;

use Plenipotentiary\Laravel\Contracts\TokenStore;

final class InMemoryTokenStore implements TokenStore
{
    /** @var array<string, array{value:string,expires:int}> */
    private array $cache = [];

    public function get(string $key): ?string
    {
        $now = time();
        if (isset($this->cache[$key]) && $this->cache[$key]['expires'] > $now) {
            return $this->cache[$key]['value'];
        }
        unset($this->cache[$key]);
        return null;
    }

    public function put(string $key, string $value, int $ttlSeconds): void
    {
        $this->cache[$key] = ['value' => $value, 'expires' => time() + $ttlSeconds];
    }

    public function forget(string $key): void
    {
        unset($this->cache[$key]);
    }

    public function expiresAt(string $key): ?int
    {
        return $this->cache[$key]['expires'] ?? null;
    }
}
```

> You can introduce a Redis-backed TokenStore later without changing the strategy contract.

---

## 5) Error Mappers (layered)

**src/Errors/DefaultErrorMapper.php**

```php
<?php
namespace Plenipotentiary\Laravel\Errors;

use Plenipotentiary\Laravel\Contracts\ErrorMapperContract;

final class DefaultErrorMapper implements ErrorMapperContract
{
    public function map(\Throwable $e): \Throwable
    {
        // TODO: Map common PSR-18/Laravel HTTP exceptions to Transport/Auth/Validation
        return $e;
    }
}
```

**src/Errors/ChainErrorMapper.php**

```php
<?php
namespace Plenipotentiary\Laravel\Errors;

use Plenipotentiary\Laravel\Contracts\ErrorMapperContract;

final class ChainErrorMapper implements ErrorMapperContract
{
    /** @param ErrorMapperContract[] $mappers */
    public function __construct(private array $mappers) {}

    public function map(\Throwable $e): \Throwable
    {
        foreach ($this->mappers as $m) { $e = $m->map($e); }
        return $e;
    }
}
```

**Provider & Resource mappers** as previously specified:

* `src/Pleni/Google/Shared/Errors/GoogleAdsErrorMapper.php`
* `src/Pleni/Google/Ads/Contexts/Search/Campaign/Errors/CampaignErrorMapper.php`

---

## 6) DTOs & Mappers (Google Ads Campaign)

As previously provided in your v1 spec:

* `CampaignOutboundDTO.php`
* `CampaignInboundDTO.php`
* `CampaignOutboundMapper.php`
* `CampaignInboundMapper.php`

---

## 7) Adapter (Google Ads Campaign)

**src/Pleni/Google/Ads/Contexts/Search/Campaign/Adapter/GoogleAdsCampaignAdapter.php**

```php
<?php
namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Plenipotentiary\Laravel\Contracts\CampaignPort;
use Plenipotentiary\Laravel\Contracts\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\InboundDTOContract;
use Plenipotentiary\Laravel\Contracts\OutboundMapperContract;
use Plenipotentiary\Laravel\Contracts\InboundMapperContract;
use Plenipotentiary\Laravel\Contracts\ErrorMapperContract;

final class GoogleAdsCampaignAdapter implements CampaignPort
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

## 8) Service Provider (bindings for adapter, errors, auth)

**src/PleniServiceProvider.php**

```php
<?php
namespace Plenipotentiary\Laravel;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\{
    CampaignPort, ErrorMapperContract, AuthStrategy, TokenStore
};
use Plenipotentiary\Laravel\Errors\ChainErrorMapper;
use Plenipotentiary\Laravel\Auth\TokenStore\InMemoryTokenStore;

final class PleniServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ----- Adapter binding (driver map) -----
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

        // ----- Error mapper binding (chain) -----
        $errCfg  = config('pleni.errors.google.ads.search.campaign', []);
        $errDrv  = data_get($errCfg, 'driver', 'default');
        $classes = data_get($errCfg, "map.$errDrv", []);
        $this->app->bind(ErrorMapperContract::class, function ($app) use ($classes) {
            $mappers = array_map(fn($c) => $app->make($c), $classes);
            return new ChainErrorMapper($mappers);
        });

        // ----- Token store (default) -----
        $this->app->singleton(TokenStore::class, InMemoryTokenStore::class);

        // ----- Auth strategy binding (driver map with overrides) -----
        $auth    = config('pleni.auth');
        $driver  = $this->resolveAuthDriver($auth);
        $class   = data_get($auth, "map.$driver");
        $options = $this->resolveAuthOptions($auth, $driver);

        if (!is_string($class) || !class_exists($class)) {
            throw new \RuntimeException("Auth strategy [$driver] not found.");
        }
        if (!is_subclass_of($class, AuthStrategy::class)) {
            throw new \RuntimeException("[$class] must implement ".AuthStrategy::class);
        }

        $this->app->bind(AuthStrategy::class, function ($app) use ($class, $options) {
            // Strategy-specific construction
            return match ($class) {
                \Plenipotentiary\Laravel\Auth\TokenAuthStrategy::class =>
                    new $class(
                        $options['header'] ?? 'Authorization',
                        $options['prefix'] ?? 'Bearer ',
                        $options['value']  ?? ''
                    ),

                \Plenipotentiary\Laravel\Auth\OAuth2ClientCredentialsStrategy::class =>
                    new $class(
                        $options['client_id']     ?? '',
                        $options['client_secret'] ?? '',
                        $options['token_url']     ?? '',
                        $options['scope']         ?? null,
                        $options['audience']      ?? null,
                        $app->make(TokenStore::class),
                        (int)($options['cache_ttl'] ?? 3300),
                    ),

                \Plenipotentiary\Laravel\Auth\HmacAuthStrategy::class =>
                    new $class(
                        $options['key_id'] ?? '',
                        $options['secret'] ?? '',
                        $options['algo']   ?? 'sha256',
                        $options['header'] ?? 'Authorization',
                        $options['prefix'] ?? 'HMAC ',
                        $options['signed_headers'] ?? ['(request-target)','date','content-digest'],
                    ),

                default => new $class(),
            };
        });
    }

    private function resolveAuthDriver(array $auth): string
    {
        // Placeholder for per-provider overrides (e.g., google.ads.search.campaign)
        // For v1, return global driver only.
        return data_get($auth, 'driver', 'noop');
    }

    private function resolveAuthOptions(array $auth, string $driver): array
    {
        // Merge global 'options.{driver}' with any override (future)
        return (array) data_get($auth, "options.$driver", []);
    }
}
```

> Note: Google Ads PHP SDK manages its own OAuth flow. The `AuthStrategy` here is primarily for HTTP-based providers. Keeping it in v1 makes the framework general and future-proof.

---

## 9) Tests

**tests/Contract/CampaignPortTest.php**

```php
<?php
it('can create a campaign via CampaignPort contract', function () {
    $dto = new \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignOutboundDTO(
        name: 'Test Campaign',
        budgetMicros: 1000000,
        advertisingChannelType: 'SEARCH'
    );

    $port = app(\Plenipotentiary\Laravel\Contracts\CampaignPort::class);

    // For a real test, mock GoogleAdsClient + mapper; here just type assertion
    expect($port)->toBeInstanceOf(\Plenipotentiary\Laravel\Contracts\CampaignPort::class);
});
```

**tests/Adapter/GoogleAdsCampaignAdapterTest.php**

```php
<?php
it('resolves to the GoogleAdsCampaignAdapter when driver=local', function () {
    config()->set('pleni.adapters.google.ads.search.campaign.driver', 'local');

    $port = app(\Plenipotentiary\Laravel\Contracts\CampaignPort::class);

    expect($port)->toBeInstanceOf(
        \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\GoogleAdsCampaignAdapter::class
    );
});
```

**tests/Auth/AuthStrategyBindingTest.php**

```php
<?php
use Plenipotentiary\Laravel\Contracts\AuthStrategy;
use GuzzleHttp\Psr7\Request;

it('binds noop auth by default', function () {
    config()->set('pleni.auth.driver', 'noop');
    $auth = app(AuthStrategy::class);
    $req = new Request('GET', 'https://example.com/foo');
    $applied = $auth->apply($req);
    expect($applied->getHeaders())->toEqual($req->getHeaders());
});

it('binds token auth and applies header', function () {
    config()->set('pleni.auth.driver', 'token');
    config()->set('pleni.auth.options.token.value', 'XYZ');
    $auth = app(AuthStrategy::class);
    $req = new Request('GET', 'https://example.com/foo');
    $applied = $auth->apply($req);
    expect($applied->getHeaderLine('Authorization'))->toBe('Bearer XYZ');
});
```

---

## 10) ENV examples

**.env**

```
# Adapter selection
PLENI_GOOGLE_ADS_SEARCH_CAMPAIGN_DRIVER=local
PLENI_ERR_GOOGLE_ADS_SEARCH_CAMPAIGN_DRIVER=default

# Auth global default
PLENI_AUTH_DRIVER=noop

# Token auth (if used)
PLENI_AUTH_TOKEN=

# OAuth2 CC (if used)
PLENI_OAUTH2_CLIENT_ID=
PLENI_OAUTH2_CLIENT_SECRET=
PLENI_OAUTH2_TOKEN_URL=
PLENI_OAUTH2_SCOPE=""

# HMAC (if used)
PLENI_HMAC_KEY_ID=
PLENI_HMAC_SECRET=
PLENI_HMAC_ALGO=sha256
```

---

## 11) Notes & Next Steps

* Google Ads adapter currently targets the PHP SDK; for pure HTTP providers, inject `AuthStrategy` into the HTTP client builder and call `apply()` per request.
* Add RedisTokenStore (optional) for multi-instance deployments.
* Expand error mappers with concrete translations once real errors are seen.
* When you’re ready, scaffold an **official** Google Ads adapter package and switch via config.

---

