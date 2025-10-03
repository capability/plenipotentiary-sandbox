# Service Provider Wiring: SDK vs REST

This document shows side-by-side how SDK-based and REST-based authentication are wired up in Laravel service providers.

## Google Ads (SDK-Based) vs Stripe (REST-Based)

### Google Ads Service Provider (SDK)

```php
// src/Pleni/Google/Ads/Shared/Providers/GoogleAdsServiceProvider.php

final class GoogleAdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Register SDK auth strategy (builds authenticated SDK client)
        $this->app->singleton(GoogleAdsSdkAuthStrategy::class, function () {
            return new GoogleAdsSdkAuthStrategy();
            // Constructor:
            //   - Builds OAuth2Credential via SDK
            //   - Builds GoogleAdsClient via SDK
            //   - Returns strategy with fully authenticated client
        });

        // 2. Register SDK client wrapper
        $this->app->singleton(GoogleAdsSdkClient::class, function ($app) {
            $strategy = $app->make(GoogleAdsSdkAuthStrategy::class);
            return new GoogleAdsSdkClient(
                $strategy->getClient() // Get GoogleAdsClient from strategy
            );
        });

        // 3. Operations inject ProviderClientContract
        $this->app->when(CampaignCreate::class)
            ->needs(ProviderClientContract::class)
            ->give(GoogleAdsSdkClient::class);
    }
}
```

**Key Points:**
- Auth strategy builds the SDK client
- `getClient()` returns authenticated `GoogleAdsClient`
- Operations use `$client->raw()->getCampaignServiceClient()`
- No HTTP headers manipulation needed

---

### Stripe Service Provider (REST)

```php
// src/Pleni/Stripe/Api/Shared/Providers/StripeServiceProvider.php

final class StripeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Register REST auth strategy (holds credentials)
        $this->app->singleton(StripeRestAuthStrategy::class, function () {
            return new StripeRestAuthStrategy(
                secretKey: StripeConfig::secretKey()
            );
            // Constructor:
            //   - Just stores the secret key
            //   - No SDK client built
            //   - apply() will add auth headers when called
        });

        // 2. Register Saloon connector (uses auth strategy)
        $this->app->singleton(StripeApiRestConnector::class, function ($app) {
            return new StripeApiRestConnector(
                authStrategy: $app->make(StripeRestAuthStrategy::class)
            );
            // Connector will use strategy in defaultHeaders()
        });

        // 3. Operations inject the connector directly
        $this->app->when(CustomerCreate::class)
            ->needs(StripeApiRestConnector::class)
            ->give(StripeApiRestConnector::class);
    }
}
```

**Key Points:**
- Auth strategy just holds credentials
- Connector uses strategy to build auth headers
- Operations use `$connector->send($request)`
- HTTP headers are explicitly set

---

## How Auth Flows Through the System

### SDK Flow (Google Ads)

```
1. App boots
   ↓
2. GoogleAdsSdkAuthStrategy constructed
   → OAuth2TokenBuilder builds credential
   → GoogleAdsClientBuilder builds authenticated client
   ↓
3. GoogleAdsSdkClient wraps the SDK client
   ↓
4. CampaignCreate injects GoogleAdsSdkClient
   ↓
5. Operation calls: $client->raw()->getCampaignServiceClient()->mutateCampaigns()
   ↓
6. SDK handles ALL HTTP communication, auth headers, token refresh internally
```

### REST Flow (Stripe)

```
1. App boots
   ↓
2. StripeRestAuthStrategy constructed
   → Just stores secret key
   ↓
3. StripeApiRestConnector constructed
   → References auth strategy
   ↓
4. CustomerCreate injects StripeApiRestConnector
   ↓
5. Operation creates Saloon Request
   ↓
6. Operation calls: $connector->send($request)
   ↓
7. Connector's defaultHeaders() uses auth strategy
   → authStrategy.getSecretKey() → build Authorization header
   ↓
8. Saloon sends HTTP request with auth headers
```

---

## Testing Implications

### Testing SDK Auth

```php
// Mock the entire SDK client
$mockClient = Mockery::mock(GoogleAdsClient::class);
$mockClient->shouldReceive('getCampaignServiceClient')
    ->andReturn($mockCampaignService);

$strategy = new GoogleAdsSdkAuthStrategy();
// Hard to test - SDK does everything internally
```

**Challenge:** SDK is opaque, hard to intercept

### Testing REST Auth

```php
// Test auth strategy directly
test('stripe auth adds correct header', function () {
    $strategy = new StripeRestAuthStrategy('sk_test_123');
    $request = new Request('GET', 'https://api.stripe.com/v1/customers');
    
    $authedRequest = $strategy->apply($request);
    
    $expected = 'Basic ' . base64_encode('sk_test_123:');
    expect($authedRequest->getHeader('Authorization')[0])->toBe($expected);
});

// Or mock the connector
$mockConnector = Mockery::mock(StripeApiRestConnector::class);
$mockConnector->shouldReceive('send')
    ->once()
    ->with(Mockery::type(Request::class))
    ->andReturn(new Response(200, [], json_encode(['id' => 'cus_123'])));
```

**Advantage:** Clean separation, easy to test each layer

---

## When Auth Happens

### SDK Auth (Eager)

```php
// Auth happens WHEN THE STRATEGY IS CONSTRUCTED
$strategy = new GoogleAdsSdkAuthStrategy();
// ↑ OAuth2 credential built here
// ↑ GoogleAdsClient built here
// ↑ All auth configuration done here

// Later, when making requests:
$client->getCampaignServiceClient()->mutateCampaigns($request);
// ↑ SDK uses pre-built auth automatically
```

**Timing:** Auth configured at construction time

### REST Auth (Lazy)

```php
// Strategy construction just stores credentials
$strategy = new StripeRestAuthStrategy('sk_test_123');
// ↑ No HTTP requests made
// ↑ No tokens fetched
// ↑ Just stores the key

// Auth happens WHEN MAKING REQUEST
$connector->send($request);
// ↑ Connector calls defaultHeaders()
// ↑ defaultHeaders() uses strategy to build auth header
// ↑ Header added to THIS specific request
```

**Timing:** Auth applied per-request

---

## OAuth Token Refresh

### SDK Handles It (Google Ads)

```php
// SDK's OAuth2TokenBuilder handles refresh automatically
$credential = (new OAuth2TokenBuilder)
    ->withClientId($clientId)
    ->withClientSecret($clientSecret)
    ->withRefreshToken($refreshToken)
    ->build();

// When token expires, SDK refreshes it automatically
// You don't see or handle this
```

### You Handle It (REST with OAuth2ClientCredentialsStrategy)

```php
// Use the built-in OAuth2 strategy for REST
final class eBayRestAuthStrategy implements AuthStrategyContract
{
    public function __construct(
        private OAuth2ClientCredentialsStrategy $oauthStrategy
    ) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        // OAuth2ClientCredentialsStrategy handles caching & refresh
        return $this->oauthStrategy->apply($request, $context);
    }
}

// Or implement your own:
public function apply(RequestInterface $request, array $context = []): RequestInterface
{
    $token = $this->tokenStore->get('ebay_token');
    
    if (!$token || $this->isExpired($token)) {
        $token = $this->refreshToken();
        $this->tokenStore->put('ebay_token', $token, $ttl);
    }
    
    return $request->withHeader('Authorization', 'Bearer ' . $token);
}
```

---

## Summary

| Aspect | SDK Auth (Google Ads) | REST Auth (Stripe) |
|--------|----------------------|-------------------|
| **Strategy builds** | Authenticated SDK client | Stores credentials only |
| **Auth timing** | Eager (at construction) | Lazy (per-request) |
| **Token refresh** | SDK handles automatically | You implement (or use OAuth2ClientCredentialsStrategy) |
| **Header injection** | SDK does it internally | Connector does it explicitly |
| **Testing** | Mock entire SDK | Mock strategy or connector |
| **Flexibility** | Limited to SDK features | Full HTTP control |
| **Dependency** | Requires vendor SDK | Just Saloon + PSR-7 |
| **Best for** | Complex SDKs with many features | Simple REST APIs |

---

## Migration Strategy

If you want to migrate from SDK to REST:

**Phase 1: Keep SDK, Add REST Auth Side-by-Side**
```php
// Keep existing
$this->app->singleton(GoogleAdsSdkAuthStrategy::class, ...);
$this->app->singleton(GoogleAdsSdkClient::class, ...);

// Add new REST auth
$this->app->singleton(GoogleAdsRestAuthStrategy::class, ...);
$this->app->singleton(GoogleAdsRestConnector::class, ...);
```

**Phase 2: Implement REST-based operations alongside SDK ones**
```php
// Old: CampaignCreate (uses SDK)
// New: CampaignCreateRest (uses Saloon)
```

**Phase 3: Test both in parallel, then remove SDK**
```php
// Remove SDK auth
// Remove SDK client
// Remove SDK-based operations
```

The CRUD adapter pattern supports both approaches seamlessly!
