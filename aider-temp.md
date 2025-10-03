
# Recommendation (short & opinionated)

* **Keep a small, stable boundary:** `RestGatewayContract` and `RestAdapterContract`.
* **Ship one blessed implementation out of the box:** **Saloon** (best DX + features).
* **Keep the door open (quietly):** because the adapter hangs off an interface, teams *can* swap in Guzzle/Laravel HTTP later—without you maintaining multiple stacks.
* **Remember:** “RestConnector” is only a **folder name** for organization, not an extra contract/layer. Developers only interact with Gateways and Adapters.

This balances pragmatism (one strong default) with flexibility (no lock-in).

---

# Contracts (add these once, keep stable)

`packages/plenipotentiary-laravel/src/Contracts/Gateway/RestGatewayContract.php`

```php
<?php
declare(strict_types=1);

namespace Capability\Pleni\Contracts\Gateway;

use Capability\Pleni\Support\Idempotency\IdempotencyHints;
use Capability\Pleni\Support\Result;

/**
 * Stable entry point for REST-style, “named operation + payload” calls.
 */
interface RestGatewayContract
{
    /**
     * @param string $operation  e.g. 'chat.completions.create'
     * @param array<string,mixed> $input
     */
    public function perform(string $operation, array $input, ?IdempotencyHints $hints = null): Result;
}
```

`packages/plenipotentiary-laravel/src/Contracts/Adapter/RestAdapterContract.php`

```php
<?php
declare(strict_types=1);

namespace Capability\Pleni\Contracts\Adapter;

use Capability\Pleni\Support\Idempotency\IdempotencyHints;
use Capability\Pleni\Support\Result;

/**
 * Provider-specific adapter for REST operations.
 * Free to use Saloon/Guzzle/Laravel HTTP or a vendor SDK internally.
 */
interface RestAdapterContract
{
    public function perform(string $operation, array $input, ?IdempotencyHints $hints = null): Result;
}
```

---

# OpenAI REST (Saloon-first) wired to contracts

**Gateway**
`packages/plenipotentiary-laravel/src/Pleni/OpenAI/Api/Contexts/Default/RestConnector/Gateway/RestGateway.php`

```php
<?php
declare(strict_types=1);

namespace Capability\Pleni\OpenAI\Api\Contexts\Default\RestConnector;

use Capability\Pleni\Contracts\Adapter\RestAdapterContract;
use Capability\Pleni\Contracts\Gateway\RestGatewayContract;
use Capability\Pleni\Support\Idempotency\IdempotencyHints;
use Capability\Pleni\Support\Result;

final class OpenAiApiRestGateway implements RestGatewayContract
{
    public function __construct(private RestAdapterContract $adapter) {}

    public function perform(string $operation, array $input, ?IdempotencyHints $hints = null): Result
    {
        // If/when you add a GatewayPolicyChain, invoke it here.
        return $this->adapter->perform($operation, $input, $hints);
    }
}
```

**Adapter (Saloon inside)**
`packages/plenipotentiary-laravel/src/Pleni/OpenAI/Api/Contexts/Default/RestConnector/Adapter/OpenAIApiRestAdapter.php`

```php
<?php
declare(strict_types=1);

namespace Capability\Pleni\OpenAI\Api\Contexts\Default;

use Capability\Pleni\Contracts\Adapter\RestAdapterContract;
use Capability\Pleni\Support\Idempotency\IdempotencyHints;
use Capability\Pleni\Support\Result;
use Capability\Pleni\OpenAI\Api\Contexts\Default\Requests\PostChatCompletionsRequest;
use Psr\Log\LoggerInterface;

final class OpenAIApiRestAdapter implements RestAdapterContract
{
    public function __construct(
        private THIS NEEDS TO BE SALOON (however that is best handled (interface vs implemntation))
        private OpenAIErrorMapper $errors,
        private LoggerInterface $log,
    ) {}

    public function perform(string $operation, array $input, ?IdempotencyHints $hints = null): Result
    {
        try {
            $response = match ($operation) {
                'chat.completions.create' => $this->connector->send(
                    new PostChatCompletionsRequest([
                        'model'    => $input['model']    ?? 'gpt-4o-mini',
                        'messages' => $input['messages'] ?? [],
                        ...array_intersect_key(
                            $input,
                            array_flip([
                                'temperature','top_p','max_tokens','response_format','tools','tool_choice','stop',
                                'frequency_penalty','presence_penalty','seed','logit_bias','user'
                            ])
                        ),
                    ])
                ),
                default => throw new \InvalidArgumentException("Unknown OpenAI operation: {$operation}"),
            };

            if ($response->failed()) {
                return $this->errors->fromResponse($response, $operation, $input);
            }

            $json = $response->json();
            if ($operation === 'chat.completions.create') {
                $choice  = $json['choices'][0] ?? null;
                $message = $choice['message'] ?? null;

                $canonical = [
                    'id'           => $json['id'] ?? null,
                    'model'        => $json['model'] ?? null,
                    'created'      => $json['created'] ?? null,
                    'content'      => is_array($message) ? ($message['content'] ?? null) : null,
                    'role'         => is_array($message) ? ($message['role'] ?? null) : null,
                    'finishReason' => $choice['finish_reason'] ?? null,
                    'usage'        => $json['usage'] ?? null,
                ];

                return Result::ok($canonical)->withMeta([
                    'provider'  => 'openai',
                    'operation' => $operation,
                    'http'      => ['status' => $response->status()],
                ]);
            }

            return Result::ok($json)->withMeta([
                'provider'  => 'openai',
                'operation' => $operation,
                'http'      => ['status' => $response->status()],
            ]);
        } catch (\Throwable $e) {
            $this->log->warning('openai.adapter.exception', ['op' => $operation, 'ex' => $e->getMessage()]);
            return $this->errors->fromException($e, $operation, $input);
        }
    }
}
```

**Connector (Saloon)** — unchanged from before, already great.

---

# Service provider bindings (interface-first, default = Saloon)

```php
use Capability\Pleni\Contracts\Gateway\RestGatewayContract;
use Capability\Pleni\Contracts\Adapter\RestAdapterContract;
use Capability\Pleni\OpenAI\Api\Contexts\Default\RestConnector\Gateway\OpenAiApiRestGateway;
use Capability\Pleni\OpenAI\Api\Contexts\Default\RestConnector\Adapter\OpenAiApiRestAdapter;

$this->app->bind(RestGatewayContract::class, OpenAiApiRestGateway::class);

// Default to Saloon-based adapter (RestAdapter). Teams can override via config later.
$this->app->bind(RestAdapterContract::class, OpenAiApiRestAdapter::class);

// If you want the gateway to depend on the interface:
$this->app->bind(OpenAiApiRestGateway::class, function ($app) {
    /** @var RestAdapterContract $adapter */
    $adapter = $app->make(RestAdapterContract::class);
    return new OpenAiApiRestGateway($adapter);
});
```

Optional config switch (kept quiet in docs, but handy):

```php
// config/pleni.php
return [
  'providers' => [
    'openai' => [
      'rest' => [
        'adapter' => \Capability\Pleni\OpenAI\Api\Contexts\Default\RestConnector\Adapter\RestAdapter::class, // swap to a guzzle-based one if desired
      ],
    ],
  ],
];
```

---

# Should the adapter be swappable across Saloon/Guzzle/Laravel HTTP?

**Trade-offs:**

* **Swappable (via interface)**

  * Future-proof; users with strict policies (no extra deps) can roll their own.
  * Keeps “ports & adapters” purity.
    − Slightly more DI glue (one interface binding).
* **Pick one tool** (Saloon only) - but allow developer to swap if needed. We just own't maintain that.

