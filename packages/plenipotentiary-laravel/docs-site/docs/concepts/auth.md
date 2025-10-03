# Authentication Strategy: SDK vs REST/Saloon

## The Key Difference

| SDK Auth — *SDK handles auth internally*            | REST Auth — *You set HTTP headers yourself*                  |
| --------------------------------------------------- | ------------------------------------------------------------ |
| Strategy builds an **authenticated SDK client**     | Strategy **stores credentials** (token/secret)               |
| `apply()` is a **no-op** (request unchanged)        | `apply()` **modifies the PSR-7 request** (adds headers)      |
| `getClient()` returns a **fully authenticated SDK** | Saloon **Connector** uses the strategy to build auth headers |

---

## Side-by-Side Comparison

| Google Ads (SDK)                                                                           | Stripe (REST)                                                                             |
| ------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------- |
| `GoogleAdsSdkAuthStrategy` implements **`SdkAuthStrategyContract`**                        | `StripeRestAuthStrategy` implements **`AuthStrategyContract`**                            |
| **Constructor:** builds `OAuth2Credential` → builds `GoogleAdsClient`; all auth configured | **Constructor:** stores secret key only; no HTTP calls; lightweight                       |
| **`apply()`**: returns request unchanged; SDK handles auth internally                      | **`apply()`**: adds `Authorization` header; encodes credentials; actually mutates request |
| **`getClient()`**: returns fully authenticated `GoogleAdsClient`                           | *(no `getClient()`; use connector + headers)*                                             |

---

## How Auth Flows Through the System

### SDK Flow (Google Ads)

1. **`GoogleAdsSdkAuthStrategy` constructed** → Builds **OAuth2Credential** via SDK → Builds **GoogleAdsClient** via SDK.
2. **`GoogleAdsSdkClient`** wraps the client and stores a reference.
3. **`CampaignCreate`** injects `GoogleAdsSdkClient` and calls `$client->raw()`.
4. **API call** → SDK adds auth internally → transport handled by SDK (HTTP/gRPC).
   ✅ You never touch HTTP headers.

### REST Flow (Stripe)

1. **`StripeRestAuthStrategy` constructed** → Stores **secret key** only (no HTTP calls yet).
2. **`StripeApiRestConnector`** constructed → Holds reference to auth strategy.
3. **`CustomerCreateRest`** injects connector → Creates a **Saloon Request**.
4. **API call** → `$connector->send($request)` → Connector’s `defaultHeaders()` uses the strategy to build **`Authorization: Basic …`** → Saloon sends the HTTP request.
   ✅ You explicitly add auth headers.

---

## Real Example: Creating a Customer

### SDK Approach *(if the vendor SDK is used)*

```php
$sdkClient = $authStrategy->getClient(); // Fully authenticated SDK
$customer  = $sdkClient->customers()->create([
    'email' => 'test@example.com',
]); // SDK adds auth under the hood
```

### REST Approach *(what we actually do)*

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
// ↑ Connector adds the Authorization header via defaultHeaders() using the auth strategy
```

---

## Stripe’s Specific Auth Pattern (safely described)

Stripe uses **HTTP Basic** where:

* **Username** is the secret key,
* **Password** is empty,
* Header is `Authorization: Basic <base64("SECRET_KEY:")>`.

**Code (safe placeholder):**

```php
public function apply(RequestInterface $request, array $context = []): RequestInterface
{
    // Use a placeholder in docs/examples; real value should come from config/env.
    $credentials = base64_encode('EXAMPLE_STRIPE_KEY:');

    return $request->withHeader('Authorization', 'Basic ' . $credentials);
}
```

**Example values (safe):**

```
Secret Key (placeholder): EXAMPLE_STRIPE_KEY
Encoded (base64 of "EXAMPLE_STRIPE_KEY:"): RVhBTVBMRV9TVFJJUEVfS0VZOg==
Header: Authorization: Basic RVhBTVBMRV9TVFJJUEVfS0VZOg==
```

---

## Testing Differences

### SDK Auth Testing *(harder)*

```php
// Must mock the SDK (opaque internals)
$mockClient = Mockery::mock(GoogleAdsClient::class);
$mockClient->shouldReceive('getCampaignServiceClient')->andReturn(/* ... */);
```

### REST Auth Testing *(easier)*

```php
test('stripe auth adds correct basic auth header', function () {
    $strategy = new StripeRestAuthStrategy('EXAMPLE_STRIPE_KEY');
    $request  = new \GuzzleHttp\Psr7\Request('GET', 'https://api.stripe.com/v1/customers');

    $authed   = $strategy->apply($request);
    $expected = 'Basic ' . base64_encode('EXAMPLE_STRIPE_KEY:');

    expect($authed->getHeader('Authorization')[0])->toBe($expected);
});
```

---

## When to Use Each Approach

### Use **SDK Auth** when:

* Vendor provides a well-maintained PHP SDK,
* SDK handles complex auth (refresh, service accounts, rotation),
* You need SDK-specific features (batch ops, field masks, streaming),
* API uses non-HTTP transport (e.g., gRPC).

*Examples:* Google Ads, AWS SDK, Facebook Marketing.

### Use **REST Auth** when:

* No SDK exists / SDK is poorly maintained,
* Simple header patterns (Bearer/Basic/API key),
* You want full control over HTTP transport,
* You need to avoid heavy SDK dependencies.

*Examples:* Stripe; many SaaS APIs (Twilio, SendGrid, Mailgun).

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

# 🔒 Copy/Paste Safety Guardrails (so this never happens again)

**Use placeholders only**

* In docs, code samples, and tests, **always** use placeholders like `EXAMPLE_STRIPE_KEY`.
* For Basic auth samples, show **base64("EXAMPLE_STRIPE_KEY:")** → `RVhBTVBMRV9TVFJJUEVfS0VZOg==`.

**Never include provider-shaped prefixes**

* Do **not** paste real-looking keys (secret or publishable) anywhere in the repo (even in comments/tests).
* Avoid mentioning exact key prefixes in code blocks; describe patterns in prose if needed.

**Load secrets from config/env**

```php
// config/services.php
'stripe' => [
    'secret' => env('STRIPE_SECRET'), // keep real value in .env or secret manager
];
```

**Local pre-commit scan (cheap & fast)**

```bash
# macOS: brew install gitleaks
# .git/hooks/pre-commit
#!/usr/bin/env bash
gitleaks detect --staged --no-banner -v || exit 1
```

**CI scan (belt & braces)**

* Add a gitleaks job to CI to scan PRs.
* Keep GitHub Push Protection enabled; **do not** unblock secrets unless it’s a confirmed false positive.

**If something slips**

1. **Rotate the key** at the provider immediately.
2. Replace examples with placeholders.
3. **Rewrite history** for the branch (e.g., `git filter-repo --replace-text`) so the secret never appears in commits.
4. Force-push the cleaned branch and proceed with a PR.

---

## Key Takeaway

**The same Adapter/Gateway contracts work for both SDK and REST approaches**, and your docs can stay **scanner-safe** with placeholders and guardrails:

```
CampaignCreate (SDK)         → uses GoogleAdsSdkClient
CustomerCreateRest (REST)    → uses StripeApiRestConnector
Both paths                   → return Result<CanonicalDTO>
```

**The CRUD pattern is transport-agnostic — and your examples should be secret-agnostic.**

