# Plenipotentiary‑Laravel Logging Guide

This guide shows how to wire **structured, PSR‑3 logging** for Plenipotentiary‑Laravel. It keeps
logging *optional* and backend‑agnostic (Loki/ELK/Datadog/etc.).

## Goals
- **Consistent** fields across gateways/adapters.
- **Optional** backend: default is noop (PSR‑3 `NullLogger`), apps can opt in.
- **Safe** by default: secrets redacted, payloads summarized.
- **Actionable**: correlation IDs, latency, idempotency, retries.

## Quick start (package)
Require PSR‑3 and provide a NullLogger fallback in your ServiceProvider:

```php
// composer.json (package)
"require": { "psr/log": "^3.0" }
```

```php
// src/Providers/PleniCoreServiceProvider.php (excerpt)
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

public function register(): void
{
    if (! $this->app->bound(LoggerInterface::class)) {
        $this->app->singleton(LoggerInterface::class, fn () => new NullLogger());
    }
}
```

Include the helper trait & redactor in your package (files below).

## Wire in the app (optional backends)
Choose any backend. Two common paths:

### A) File → Promtail → Loki (recommended)
`config/logging.php`:

```php
'channels' => [
    'json' => [
        'driver'    => 'single',
        'path'      => storage_path('logs/laravel.json'),
        'tap'       => [App\Logging\AddPleniProcessor::class],
        'formatter' => Monolog\Formatter\JsonFormatter::class,
    ],
    'stack' => [
        'driver' => 'stack',
        'channels' => ['json'], // add 'daily', 'stderr' as needed
    ],
];
```

Run Promtail to ship the JSON file to Grafana Loki.

### B) Direct push (Monolog handler)
Create a custom `LokiHandler` and add a `loki` channel. Then add that channel to `stack`.
(See README example in the queue/logging docs.)

## Stable log keys
Pleni emits/encourages these keys (flat for easy querying):

- `corr` — correlation id
- `pleni.provider` — e.g. `google`
- `pleni.domain` — e.g. `ads.search`
- `pleni.resource` — e.g. `campaign`
- `pleni.op` — `create|read|update|delete|search`
- `id.external` — provider resource id (e.g. `resourceName`), if known
- `id.internal` — safe internal id/business key
- `latency_ms`, `provider_latency_ms`
- `idempotency.fp`, `idempotency.event` — `short_circuit|cache_hit|tombstone`
- `retry.attempt`, `retry.max`, `rate.limited`, `rate.retry_after_ms`
- `http.method`, `http.url_host`, `http.url_path`, `http.status`
- `error.type`, `error.code`, `error.message_redacted`
- `pagination.next` (bool), `pagination.cursor_hash`

> **Never** log raw tokens, full query strings, or PII. Use the `Redactor` helper.

## Using the PleniLoggerTrait
Apply `PleniLoggerTrait` in gateways/adapters to log consistently:

```php
use Plenipotentiary\Laravel\Support\Logging\PleniLoggerTrait;

final class CampaignApiCrudGateway
{
    use PlenipotentiaryLoggerTrait;

    public function __construct(private \Psr\Log\LoggerInterface $log) {}

    public function create(OutboundDTOContract $dto): InboundDTOContract
    {
        $ctx = $this->pleniCtx('google', 'ads.search', 'campaign', 'create', [
            'id.internal' => $dto->internalId ?? null,
        ]);

        $t0 = hrtime(true);
        $this->pleniInfo('pleni.create.start', $ctx);

        // ... perform create (idempotency, adapter call etc.)

        $in = $this->adapter->create($dto);

        $this->pleniInfo('pleni.create.ok', $ctx + [
            'id.external' => method_exists($in, 'resourceName') ? $in->resourceName() : null,
            'latency_ms'  => (int) ((hrtime(true) - $t0) / 1_000_000),
        ]);

        return $in;
    }
}
```

## Redaction & hashing
Use `Redactor` anywhere you add request/response data to logs:

```php
$cleanHeaders = Redactor::headers($rawHeaders);
$cleanBody    = Redactor::body($rawBodyArray, fieldsToHash: ['email']);
$cursorHash   = Redactor::hash($cursor);
```

## Levels
- `info` for lifecycle: `*.start|ok|cache_hit|short_circuit|tombstone`
- `warning` for recoverable issues (rate limit, temporary errors)
- `error` for mapped final failures
- `debug` for development; include only **redacted** payload shapes

## Sampling
- Prefer debug logs off in production.
- Sample noisy events (e.g. search pages) at 1–5% if volume is high.

## Testing
- Use `Log::spy()` to assert records or inject a test logger.
- Ensure no secrets appear in `error.message_redacted` or redacted fields.
- Couple with event tests if you enabled Pleni gateway events.

## Do / Don’t
- ✅ Include `corr`, provider/domain/resource/op on every line.
- ✅ Redact tokens, hash cursor/ids as needed.
- ✅ Log idempotency & retry decisions.
- ❌ Don’t log full URLs with query strings.
- ❌ Don’t log whole request/response bodies; summarize & redact.
