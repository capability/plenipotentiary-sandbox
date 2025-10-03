# Authentication Strategy: SDK vs REST/Saloon

## The Key Difference

| SDK Auth — _SDK handles auth internally_            | REST Auth — _You set HTTP headers yourself_                  |
| --------------------------------------------------- | ------------------------------------------------------------ |
| Strategy builds an **authenticated SDK client**     | Strategy **stores credentials** (token/secret)               |
| `apply()` is a **no-op** (request unchanged)        | `apply()` **modifies the PSR-7 request** (adds headers)      |
| `getClient()` returns a **fully authenticated SDK** | Saloon **Connector** uses the strategy to build auth headers |

---

## Side-by-Side Comparison

| Google Ads (SDK)                                                                           | Stripe (REST)                                                                                    |
| ------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------ |
| `GoogleAdsSdkAuthStrategy` implements **`SdkAuthStrategyContract`**                        | `StripeRestAuthStrategy` implements **`AuthStrategyContract`**                                   |
| **Constructor:** builds `OAuth2Credential` → builds `GoogleAdsClient`; all auth configured | **Constructor:** stores secret key only; no HTTP calls; lightweight                              |
| **`apply()`**: returns request unchanged; SDK handles auth internally                      | **`apply()`**: adds `Authorization` header; base64 encodes credentials; actually mutates request |
| **`getClient()`**: returns fully authenticated `GoogleAdsClient`                           | _(no `getClient()`; use connector + headers)_                                                    |

---

## How Auth Flows Through the System

### SDK Flow (Google Ads)

1. **`GoogleAdsSdkAuthStrategy` constructed**
   → Builds **OAuth2Credential** via SDK
   → Builds **GoogleAdsClient** via SDK
   → Returns strategy with authenticated client

2. **`GoogleAdsSdkClient`** wraps the client
   → Stores reference to `GoogleAdsClient`

3. **`CampaignCreate`** injects `GoogleAdsSdkClient`
   → Gets client via `$client->raw()`

4. **API call**
   → `$client->getCampaignServiceClient()->mutateCampaigns($request)`
   → SDK adds auth headers internally
   → SDK makes HTTP/gRPC call
   ✅ You never touch HTTP headers

---

### REST Flow (Stripe)

1. **`StripeRestAuthStrategy` constructed**
   → Stores **secret key** only
   → No HTTP calls yet

2. **`StripeApiRestConnector`** constructed
   → Holds reference to auth strategy

3. **`CustomerCreateRest`** injects connector
   → Creates **Saloon Request**

4. **API call**
   → `$connector->send($request)`
   → Connector’s `defaultHeaders()` called
   → Uses `authStrategy->getSecretKey()`
   → Builds `Authorization: Basic …` header
   → Saloon sends HTTP request
   ✅ You explicitly add auth headers

---

## Real Example: Creating a Customer

### SDK Approach _(if Stripe had/used an SDK)_

```php
$sdkClient = $authStrategy->getClient(); // Fully authenticated SDK
$customer = $sdkClient->customers()->create([
    'email' => 'test@example.com',
]);
// SDK adds "Authorization: Bearer sk_xxx" internally
```

### REST Approach _(what we actually do)_

```php
use Saloon\Enums\Method;
use Saloon\Http\Request;

$request = new class extends Request {
    protected Method $method = Method::POST;
    public function resolveEndpoint(): string {
        return '/v1/customers';
    }
};

$response = $connector->send($request);
// ↑ Connector adds "Authorization: Basic c2tfeHh4Og==" via defaultHeaders() using the auth strategy
```

---

## Stripe’s Specific Auth Pattern

Stripe uses **HTTP Basic** with a twist:

- **Username:** your secret key (`***REMOVED***xxx` or `***REMOVED***xxx`)
- **Password:** empty string
- **Header:** `Authorization: Basic <base64(secret_key:)>`

**Code:**

```php
public function apply(RequestInterface $request, array $context = []): RequestInterface
{
    // Encode "***REMOVED***123:" (note the colon and empty password)
    $credentials = base64_encode($this->secretKey . ':');

    return $request->withHeader('Authorization', 'Basic ' . $credentials);
}
```

**Example:**

```
Secret Key:
Encoded:    
Header:     Authorization: Basic PLACEHOLER_SEC
```

---

## Testing Differences

### SDK Auth Testing _(harder)_

```php
// Must mock the SDK
$mockClient = Mockery::mock(GoogleAdsClient::class);
$mockClient->shouldReceive('getCampaignServiceClient')->andReturn(/* ... */);
// SDK is opaque — hard to test auth in isolation
```

### REST Auth Testing _(easier)_

```php
test('stripe auth adds correct basic auth header', function () {
    $strategy = new StripeRestAuthStrategy('***REMOVED***123');
    $request  = new \GuzzleHttp\Psr7\Request('GET', 'https://api.stripe.com/v1/customers');

    $authed   = $strategy->apply($request);
    $expected = 'Basic ' . base64_encode('***REMOVED***123:');

    expect($authed->getHeader('Authorization')[0])->toBe($expected);
});
```

---

## When to Use Each Approach

### Use **SDK Auth** when:

- Vendor provides a well-maintained PHP SDK
- SDK handles complex auth (OAuth refresh, service accounts, rotating keys)
- You need SDK-specific features (batch ops, field masks, streaming)
- API uses non-HTTP transport (gRPC/WebSockets)

_Examples:_ Google Ads, Facebook Marketing, AWS SDK

### Use **REST Auth** when:

- No SDK exists / SDK is poorly maintained
- Simple auth pattern (Bearer, Basic, API key)
- You want full control over HTTP transport
- You need to avoid heavy SDK dependencies
- API is straightforward REST

_Examples:_ Stripe; most SaaS APIs (Twilio, SendGrid, Mailgun)

---

## Files Created for Stripe Auth

```
src/Pleni/Stripe/Api/Shared/
  ├─ Auth/
  │   └─ StripeRestAuthStrategy.php      ← Implements AuthStrategyContract
  ├─ Support/
  │   └─ StripeConfig.php                ← Config loader
  ├─ Providers/
  │   └─ StripeServiceProvider.php       ← Wires up auth + connector
  └─ Transfer/Rest/
      └─ StripeApiRestConnector.php      ← Uses auth strategy

Documentation:
  ├─ AUTH_STRATEGY_COMPARISON.md         ← Explains SDK vs REST auth
  └─ WIRING_COMPARISON.md                ← Shows service provider setup
```

---

## Key Takeaway

**The same Adapter/Gateway contracts work for both SDK and REST approaches.**

```
CampaignCreate (SDK)         → uses GoogleAdsSdkClient
CustomerCreateRest (REST)    → uses StripeApiRestConnector
Both paths                   → return Result<CanonicalDTO>
```

**The CRUD pattern is transport-agnostic.**
