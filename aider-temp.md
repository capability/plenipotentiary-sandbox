Short answer: **yes—but narrowly.**
Pleni should have a **small, deterministic plugin (policy) pipeline** at the *Gateway* layer so teams can swap in org-specific behaviour (e.g., custom retry policy, rate limiter, logging/PII redaction) without forking Pleni. Think “**Gateway Policies**,” not a wild west of extensions.

Here’s a concrete way to do it that keeps Pleni opinionated:

# What to allow

* **Cross-cutting, provider-agnostic concerns only:** retries, backoff, circuit-breaker, rate limiting, timeouts, PII redaction, metrics/telemetry, tracing, audit logging, chaos testing.
* **No request-shape coupling:** Plugins shouldn’t know about Google Ads vs eBay DTO fields.

# What to avoid

* Plugins that **modify canonical DTOs** or **encode provider logic**. Keep that in Adapters.
* Plugin chains that are **non-deterministic** or order-dependent with hidden side effects.

# Minimal design (stable & testable)

## 1) Define a tiny policy interface

```php
namespace Pleni\Contracts;

use Pleni\Support\GatewayCall;   // contains op name, input, hints, context bag
use Pleni\Support\Result;

interface GatewayPolicy
{
    /** Called before the adapter call. May block (e.g., rate limit) or wrap context. */
    public function before(GatewayCall $call): GatewayCall;

    /** Called after a successful adapter call. May augment meta/telemetry only. */
    public function after(GatewayCall $call, Result $result): Result;

    /** Called when adapter throws or returns failure. May transform error or decide retry. */
    public function onError(GatewayCall $call, \Throwable|Result $error): Result;
}
```

* **Contract guarantees:**

  * `before` can *enrich context* (e.g., correlation IDs) and perform **pre-checks** (rate limit, circuit breaker).
  * `after` can add **metadata/metrics**, but **must not mutate canonical `data()`**.
  * `onError` can **map errors** and **signal retry** (by throwing a `RetryRequested` exception or returning a `Result::retryable(...)`).

## 2) Deterministic policy chain

```php
final class GatewayPolicyChain
{
    /** @param array<GatewayPolicy> $policies ordered */
    public function __construct(private array $policies) {}

    public function invoke(callable $adapterCall, GatewayCall $call): Result
    {
        $c = $call;
        foreach ($this->policies as $p) $c = $p->before($c);

        try {
            $res = $adapterCall($c); // Adapter returns Result
            foreach (array_reverse($this->policies) as $p) $res = $p->after($c, $res);
            return $res;
        } catch (\Throwable $e) {
            $err = $e;
            foreach (array_reverse($this->policies) as $p) $err = $p->onError($c, $err);
            return $err instanceof Result ? $err : Result::error('UNHANDLED', $e->getMessage(), ['ex'=>$e]);
        }
    }
}
```

* **Ordering is explicit** (constructor order) → great for tests and predictability.
* **Reverse order** on the way out mirrors HTTP middleware semantics.

## 3) Registration via Laravel config/service provider

```php
// config/pleni.php
return [
  'policies' => [
    \Pleni\Policies\CorrelationIdPolicy::class,
    \Pleni\Policies\RateLimitPolicy::class,
    \Pleni\Policies\RetryBackoffPolicy::class,
    \Pleni\Policies\LoggingPolicy::class,
    \Pleni\Policies\MetricsPolicy::class,
  ],
];
```

```php
// PleniCoreServiceProvider.php
$this->app->bind(GatewayPolicyChain::class, function ($app) {
    $policies = collect(config('pleni.policies', []))
        ->map(fn ($c) => $app->make($c))
        ->all();
    return new GatewayPolicyChain($policies);
});
```

## 4) Example policies (sketches)

**Retry + backoff (idempotency-aware)**

```php
final class RetryBackoffPolicy implements GatewayPolicy
{
    public function before(GatewayCall $call): GatewayCall { return $call; }

    public function after(GatewayCall $call, Result $res): Result { return $res; }

    public function onError(GatewayCall $call, \Throwable|Result $err): Result
    {
        $retryable = $err instanceof Result
            ? $err->isRetryable()
            : $err instanceof \Pleni\Exceptions\TransientNetworkError;

        if ($retryable && $call->hints->retriesRemaining() > 0) {
            $delay = $call->hints->nextBackoffMs();
            usleep($delay * 1000);
            throw new \Pleni\Support\RetryRequested(); // gateway catches and re-invokes adapter
        }

        return $err instanceof Result ? $err : Result::error('TRANSIENT', (string)$err);
    }
}
```

**Token bucket rate limiter**

```php
final class RateLimitPolicy implements GatewayPolicy
{
    public function __construct(private RateLimiter $limiter) {}

    public function before(GatewayCall $call): GatewayCall
    {
        $key = "rate:{$call->provider}:{$call->operation}";
        $this->limiter->consumeOrBlock($key);
        return $call;
    }
    public function after(GatewayCall $call, Result $res): Result { return $res; }
    public function onError(GatewayCall $call, \Throwable|Result $e): Result { return $e instanceof Result ? $e : Result::error('ERR', $e->getMessage()); }
}
```

**Structured logging + PII redaction**

```php
final class LoggingPolicy implements GatewayPolicy
{
    public function __construct(private LoggerInterface $log, private Redactor $redact) {}

    public function before(GatewayCall $c): GatewayCall {
        $this->log->info('pleni.call.start', ['op'=>$c->operation,'corr'=>$c->context['corr'] ?? null]);
        return $c;
    }
    public function after(GatewayCall $c, Result $r): Result {
        $this->log->info('pleni.call.ok', ['op'=>$c->operation,'meta'=>$this->redact->meta($r->meta())]);
        return $r;
    }
    public function onError(GatewayCall $c, \Throwable|Result $e): Result {
        $this->log->warning('pleni.call.fail', ['op'=>$c->operation,'err'=>$this->redact->error($e)]);
        return $e instanceof Result ? $e : Result::error('FAILED', $e->getMessage());
    }
}
```

# Why this works for Pleni

* **Keeps Pleni opinionated** (canonical DTOs, Result object, idempotency hints) while letting teams **swap policies** without touching Adapters.
* **Interops with Saloon** cleanly: Adapters can use Saloon/Guzzle/SDKs; Policies don’t care.
* **Deterministic & testable**: explicit order; each policy can be unit-tested in isolation.
* **Safe boundaries**: policies can’t mutate canonical `data()`; only meta/telemetry.

# Naming & docs

* Call them **“Gateway Policies (Plugins)”** in docs. Lead with the defaults:

  * `CorrelationIdPolicy`, `RateLimitPolicy`, `RetryBackoffPolicy`, `CircuitBreakerPolicy`, `LoggingPolicy`, `MetricsPolicy`, `ChaosPolicy` (disabled by default).
* Document **ordering** and a simple rule: *“Policies must not alter canonical data; do observe/augment meta.”*

# Migration path (you can do this incrementally)

1. Implement `GatewayPolicy` + `GatewayPolicyChain`.
2. Wrap existing gateway adapter calls with the chain.
3. Move current built-ins (retry, logging, idempotency glue) behind default policies.
4. Expose `pleni.policies` config for overrides.
5. Ship 3–4 blessed policies; keep the interface stable.

---

**Bottom line:** add plugins, but **keep the surface small and disciplined**. You get extensibility where it matters (ops concerns) without weakening Pleni’s core promise: stable gateways, clean adapters, predictable results.

