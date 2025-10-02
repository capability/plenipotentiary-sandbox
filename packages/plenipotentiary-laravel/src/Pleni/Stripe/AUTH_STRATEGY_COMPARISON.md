# Authentication Strategy: SDK vs REST/Saloon

This document explains how authentication works differently for SDK-based and REST-based integrations.

## The Two Approaches

### 1. SDK-Based Authentication (Google Ads Example)

**Key Characteristic:** The vendor SDK handles all authentication internally.

```php
// GoogleAdsSdkAuthStrategy.php
final class GoogleAdsSdkAuthStrategy implements SdkAuthStrategyContract
{
    private GoogleAdsClient $client;

    public function __construct()
    {
        // Build OAuth2 credentials using SDK's builder
        $oAuth2Credential = (new OAuth2TokenBuilder)
            ->withClientId(GoogleAdsConfig::clientId())
            ->withClientSecret(GoogleAdsConfig::clientSecret())
            ->withRefreshToken(GoogleAdsConfig::refreshToken())
            ->build();

        // Build authenticated client using SDK's builder
        $this->client = (new GoogleAdsClientBuilder)
            ->withOAuth2Credential($oAuth2Credential)
            ->withDeveloperToken(GoogleAdsConfig::developerToken())
            ->build();
    }

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        // No-op! SDK handles auth internally
        return $request;
    }

    public function getClient(): GoogleAdsClient
    {
        return $this->client; // Return fully authenticated SDK client
    }
}
```

**How It Works:**
1. ✅ SDK provides builders for OAuth2, client configuration
2. ✅ SDK handles token refresh, header injection, etc.
3. ✅ Strategy just builds and returns the authenticated SDK client
4. ✅ `apply()` is a no-op passthrough (SDK does everything)
5. ✅ Operations use `$client->getCampaignServiceClient()->mutateCampaigns()`

**Benefits:**
- SDK handles all complexity (OAuth refresh, gRPC setup, etc.)
- Strong typing (SDK objects like `Campaign`, `CampaignOperation`)
- Feature-rich (field masks, partial failure, response types)

**Trade-offs:**
- Requires vendor SDK dependency
- Less control over HTTP transport
- SDK must be kept up-to-date

---

### 2. REST-Based Authentication (Stripe Example)

**Key Characteristic:** We directly manipulate HTTP headers for authentication.

```php
// StripeRestAuthStrategy.php
final class StripeRestAuthStrategy implements AuthStrategyContract
{
    public function __construct(private string $secretKey) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        // Directly add authentication header
        // Stripe uses HTTP Basic Auth: secret_key as username, empty password
        $credentials = base64_encode($this->secretKey . ':');
        
        return $request->withHeader('Authorization', 'Basic ' . $credentials);
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }
}
```

**How It Works:**
1. ✅ Strategy receives secret key from config
2. ✅ `apply()` actually modifies the PSR-7 request (adds auth header)
3. ✅ Saloon connector uses this strategy to authenticate all requests
4. ✅ No SDK needed - we're making direct HTTP calls

**Connector Integration:**

```php
// StripeApiRestConnector.php
final class StripeApiRestConnector extends Connector
{
    public function __construct(
        private ?StripeRestAuthStrategy $authStrategy = null,
    ) {}

    protected function defaultHeaders(): array
    {
        $authStrategy = $this->authStrategy ?? new StripeRestAuthStrategy(
            StripeConfig::secretKey()
        );

        return [
            'Authorization' => 'Basic ' . base64_encode($authStrategy->getSecretKey() . ':'),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Stripe-Version' => StripeConfig::apiVersion(),
        ];
    }
}
```

**Benefits:**
- Full control over HTTP transport
- No SDK dependency (just Saloon)
- Can test/mock auth easily
- Can adapt to any API quirks

**Trade-offs:**
- Must handle token refresh manually (if using OAuth)
- Must implement API-specific auth patterns
- No SDK helpers for complex operations

---

## Contract Hierarchy

```
AuthStrategyContract (base for REST/HTTP)
├── apply(RequestInterface): RequestInterface  ← Actually modifies request
│
└── SdkAuthStrategyContract (extends AuthStrategyContract)
    ├── apply(RequestInterface): RequestInterface  ← Usually a no-op
    └── getClient(): object  ← Returns authenticated SDK client
```

### When to Implement Which Contract

**Use `AuthStrategyContract` when:**
- Working with REST APIs via Saloon/HTTP clients
- Direct HTTP header manipulation
- Examples: TokenAuthStrategy, OAuth2ClientCredentialsStrategy, StripeRestAuthStrategy

**Use `SdkAuthStrategyContract` when:**
- Working with vendor SDKs
- SDK handles auth internally
- Examples: GoogleAdsSdkAuthStrategy, eBaySdkAuthStrategy

---

## Real-World Authentication Patterns

### Pattern 1: Simple Bearer Token (OpenAI, Many SaaS APIs)

```php
final class OpenAIRestAuthStrategy implements AuthStrategyContract
{
    public function __construct(private string $apiKey) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        return $request->withHeader('Authorization', 'Bearer ' . $this->apiKey);
    }
}
```

**Used by:** OpenAI, GitHub, Stripe (alternative), many SaaS APIs

### Pattern 2: HTTP Basic Auth (Stripe, Twilio)

```php
final class StripeRestAuthStrategy implements AuthStrategyContract
{
    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $credentials = base64_encode($this->secretKey . ':');
        return $request->withHeader('Authorization', 'Basic ' . $credentials);
    }
}
```

**Used by:** Stripe, Twilio, Mailgun

### Pattern 3: OAuth 2.0 Client Credentials (eBay, Many Enterprise APIs)

```php
final class OAuth2ClientCredentialsStrategy implements AuthStrategyContract
{
    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $token = $this->getOrRefreshToken(); // Fetches/caches token
        return $request->withHeader('Authorization', 'Bearer ' . $token);
    }

    private function getOrRefreshToken(): string
    {
        // Check cache, fetch if expired, cache new token
    }
}
```

**Used by:** eBay, PayPal, many enterprise APIs

### Pattern 4: HMAC Signature (AWS, Custom APIs)

```php
final class HmacAuthStrategy implements AuthStrategyContract
{
    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $signature = $this->generateSignature($request);
        return $request
            ->withHeader('X-Signature', $signature)
            ->withHeader('X-Timestamp', time());
    }
}
```

**Used by:** AWS, custom internal APIs

### Pattern 5: SDK Handles Everything (Google Ads, Facebook)

```php
final class GoogleAdsSdkAuthStrategy implements SdkAuthStrategyContract
{
    private GoogleAdsClient $client;

    public function __construct()
    {
        // SDK builders handle all auth complexity
        $this->client = (new GoogleAdsClientBuilder)
            ->withOAuth2Credential($credential)
            ->withDeveloperToken($token)
            ->build();
    }

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        return $request; // No-op, SDK does everything
    }

    public function getClient(): GoogleAdsClient
    {
        return $this->client; // Return authenticated SDK
    }
}
```

**Used by:** Google Ads, Facebook Marketing API (when using their SDKs)

---

## Testing Authentication

### Testing REST Auth (Direct Mocking)

```php
test('stripe auth adds basic auth header', function () {
    $strategy = new StripeRestAuthStrategy('***REMOVED***123');
    
    $request = new Request('GET', 'https://api.stripe.com/v1/customers');
    $authenticatedRequest = $strategy->apply($request);
    
    expect($authenticatedRequest->hasHeader('Authorization'))->toBeTrue();
    expect($authenticatedRequest->getHeader('Authorization')[0])
        ->toStartWith('Basic ');
});
```

### Testing SDK Auth (Mock the SDK)

```php
test('google ads auth returns authenticated client', function () {
    $strategy = new GoogleAdsSdkAuthStrategy();
    $client = $strategy->getClient();
    
    expect($client)->toBeInstanceOf(GoogleAdsClient::class);
});
```

---

## Migration Path: SDK → REST

If you start with an SDK and later want to move to REST (for more control):

**Before (SDK):**
```php
GoogleAdsSdkAuthStrategy → Returns GoogleAdsClient
CampaignCreate → Uses $client->getCampaignServiceClient()
```

**After (REST):**
```php
GoogleAdsRestAuthStrategy → Adds OAuth bearer token to headers
CampaignCreateRest → Uses Saloon to POST to /v1/customers/:id/campaigns
```

The CRUD operations remain the same structure - only the transport changes!

---

## Key Takeaways

| Aspect | SDK Auth | REST Auth |
|--------|----------|-----------|
| **Contract** | `SdkAuthStrategyContract` | `AuthStrategyContract` |
| **apply() behavior** | No-op passthrough | Modifies HTTP headers |
| **Primary method** | `getClient()` returns SDK | `apply()` adds auth headers |
| **Token refresh** | SDK handles it | You handle it (or use OAuth2ClientCredentialsStrategy) |
| **Testing** | Mock SDK | Mock HTTP client or test headers |
| **Flexibility** | Limited to SDK features | Full HTTP control |
| **Complexity** | SDK does everything | You implement auth logic |
| **Best for** | Vendor provides good SDK | Simple APIs, need control, no SDK |

---

## Recommendations

**Use SDK Auth when:**
- ✅ Vendor provides a well-maintained PHP SDK
- ✅ SDK handles complex auth (OAuth refresh, service accounts, etc.)
- ✅ You need SDK-specific features (field masks, batch operations)
- ✅ Examples: Google Ads, Facebook Marketing

**Use REST Auth when:**
- ✅ No SDK available or SDK is poorly maintained
- ✅ Simple auth pattern (Bearer token, Basic auth)
- ✅ Need full control over HTTP transport
- ✅ Want to avoid heavy SDK dependencies
- ✅ Examples: Stripe (has SDK but REST is simpler), most SaaS APIs

**Use Both when:**
- ✅ Providing options to end users
- ✅ Supporting multiple auth methods (API key + OAuth)
- ✅ Migrating from SDK to REST (run both in parallel)
