Perfect—let’s keep your **single canonical DTO** named `CampaignInboundDTO` and keep it **in the provider context** (so call sites read nicely), but make it **provider-agnostic** and **contract-driven** via your existing `Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract`. Then the **adapter** (and only the adapter) knows the Google Ads SDK and maps into this DTO.

Below are two drop-in files:

---

# `Google/Ads/Contexts/Search/Campaign/DTO/CampaignInboundDTO.php`

```php
<?php

namespace Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;

/**
 * Canonical, provider-agnostic inbound DTO for a Campaign.
 * Lives in the Google/Ads context for ergonomics, but has ZERO SDK fields.
 * All provider-specific detail is pushed into namespaced attributes by the Adapter.
 */
final class CampaignInboundDTO implements InboundDTOContract
{
    /** Fixed for this DTO */
    private string $resource = 'campaign';

    /** add|update|remove|get|unknown */
    private string $operation = 'unknown';

    /** Canonical external identifier (string to avoid int overflow across providers) */
    private ?string $externalId = null;

    /** Human-readable label/name if available */
    private ?string $label = null;

    /**
     * Canonical lifecycle state normalized by the adapter:
     * active|paused|disabled|draft|archived|deleted|unknown
     */
    private string $state = 'unknown';

    /** Cross-cutting metadata (e.g., requestId, pagination keys) */
    private array $meta = [];

    /**
     * Provider-specific attributes, namespaced.
     * e.g. ['google_ads.ads.resource_name' => 'customers/123/campaigns/456']
     */
    private array $attributes = [];

    /** Warnings/soft errors captured while mapping */
    private array $warnings = [];

    /** Lightweight raw snapshot (arrays only; never keep SDK objects here) */
    private array $raw = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }

    /* ---------------- Contract-style accessors ---------------- */

    public function getResource(): string { return $this->resource; }
    public function getOperation(): string { return $this->operation; }
    public function getExternalId(): ?string { return $this->externalId; }
    public function getLabel(): ?string { return $this->label; }
    public function getState(): string { return $this->state; }
    public function getMeta(): array { return $this->meta; }
    public function getAttributes(): array { return $this->attributes; }
    public function getWarnings(): array { return $this->warnings; }
    public function getRaw(): array { return $this->raw; }

    public function toArray(): array
    {
        return [
            'resource'   => $this->resource,
            'operation'  => $this->operation,
            'externalId' => $this->externalId,
            'label'      => $this->label,
            'state'      => $this->state,
            'meta'       => $this->meta,
            'attributes' => $this->attributes,
            'warnings'   => $this->warnings,
            'raw'        => $this->raw,
        ];
    }

    /* ---------------- Optional fluent setters (handy in tests) ---------------- */

    public function withOperation(string $op): self { $this->operation = $op; return $this; }
    public function withExternalId(?string $id): self { $this->externalId = $id; return $this; }
    public function withLabel(?string $label): self { $this->label = $label; return $this; }
    public function withState(string $state): self { $this->state = $state; return $this; }
    public function withMeta(array $meta): self { $this->meta = $meta; return $this; }
    public function withAttributes(array $attrs): self { $this->attributes = $attrs; return $this; }
    public function withWarnings(array $warnings): self { $this->warnings = $warnings; return $this; }
    public function withRaw(array $raw): self { $this->raw = $raw; return $this; }
}
```

> ✅ This satisfies `InboundDTOContract` via common getters + `toArray()`, is **agnostic** (no Google fields), and remains **context-local** (`CampaignInboundDTO`) for readability.

---

# `Google/Ads/Contexts/Search/Campaign/Adapter/AdapterSupport/CampaignApiInboundDTOAdapter.php`

```php
<?php

namespace Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\AdapterSupport;

use Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignInboundDTO;

/**
 * Knows the Google Ads SDK shape. Produces provider-agnostic CampaignInboundDTOs.
 * - Duck-typed calls keep us light on hard SDK coupling.
 * - Namespaced attributes carry provider-specific details.
 */
final class CampaignApiInboundDTOAdapter
{
    /**
     * Build DTOs from a mutate response (add/update/remove).
     * Accepts:
     *  - MutateCampaignsResponse (->getResults())
     *  - iterable/array of MutateCampaignResult
     */
    public static function fromMutateResponse(object|iterable $response, string $operation, ?string $requestId = null): array
    {
        $dtos = [];
        foreach (self::extractResults($response) as $result) {
            $resourceName = self::call($result, 'getResourceName');

            $dto = new CampaignInboundDTO([
                'operation'  => $operation,
                'meta'       => ['requestId' => $requestId],
                'attributes' => [
                    'google_ads.ads.resource_name' => $resourceName,
                ],
                // externalId is typically not returned on mutate without a read-back
                'raw'        => ['resourceName' => $resourceName],
            ]);

            $dtos[] = $dto;
        }
        return $dtos;
    }

    /**
     * Build a DTO from a single GoogleAdsRow (search/get).
     * Expected fields (per sample queries): campaign.id, campaign.name (+ optional status, channel).
     */
    public static function fromSearchRow(object $row, ?string $requestId = null): CampaignInboundDTO
    {
        $campaign = self::call($row, 'getCampaign');

        $id      = self::call($campaign, 'getId');
        $name    = self::call($campaign, 'getName');
        $status  = self::call($campaign, 'getStatus');
        $channel = self::call($campaign, 'getAdvertisingChannelType');
        $rn      = self::call($campaign, 'getResourceName');

        return new CampaignInboundDTO([
            'operation'  => 'get',
            'externalId' => $id !== null ? (string)$id : null,
            'label'      => $name ?: null,
            'state'      => self::mapStatusToState($status),
            'meta'       => ['requestId' => $requestId],
            'attributes' => array_filter([
                'google_ads.ads.resource_name'            => $rn,
                'google_ads.ads.advertising_channel_type' => $channel,
                'google_ads.ads.status_raw'               => $status,
            ]),
            'raw'        => array_filter([
                'resourceName' => $rn,
                'id'           => $id,
                'name'         => $name,
                'status'       => $status,
                'channel'      => $channel,
            ]),
        ]);
    }

    /**
     * Convert a GoogleAdsException (or generic Exception) into a non-throwing DTO.
     * Useful for flowing errors through the same pipeline in tests.
     */
    public static function fromException(object $ex): CampaignInboundDTO
    {
        $requestId = self::call($ex, 'getRequestId');
        $warnings  = [];

        $failure = self::call($ex, 'getGoogleAdsFailure');
        if ($failure && method_exists($failure, 'getErrors')) {
            foreach ($failure->getErrors() as $err) {
                $codeObj = self::call($err, 'getErrorCode');
                $code    = (is_object($codeObj) && method_exists($codeObj, 'getErrorCode'))
                    ? $codeObj->getErrorCode()
                    : null;
                $msg = self::call($err, 'getMessage') ?? 'Unknown error';
                $warnings[] = trim(($code ? $code . ': ' : '') . $msg);
            }
        } elseif (method_exists($ex, 'getMessage')) {
            $warnings[] = $ex->getMessage();
        }

        return new CampaignInboundDTO([
            'operation'  => 'unknown',
            'meta'       => ['requestId' => $requestId],
            'warnings'   => $warnings,
            'attributes' => [],
            'raw'        => ['exceptionClass' => get_class($ex)],
        ]);
    }

    /* ----------------------- helpers ----------------------- */

    private static function extractResults(object|iterable $response): iterable
    {
        if (is_iterable($response)) return $response;

        if (method_exists($response, 'getResults')) {
            $results = $response->getResults();
            if (is_iterable($results)) return $results;
        }
        if (property_exists($response, 'results') && is_iterable($response->results)) {
            return $response->results;
        }
        return [];
    }

    private static function call(?object $obj, string $method): mixed
    {
        return $obj && method_exists($obj, $method) ? $obj->$method() : null;
    }

    private static function mapStatusToState(mixed $status): string
    {
        // Normalize provider-specific statuses -> canonical states
        return match ((string)$status) {
            'ENABLED'     => 'active',
            'PAUSED'      => 'paused',
            'REMOVED'     => 'deleted',
            'UNKNOWN', '' => 'unknown',
            default       => 'unknown',
        };
    }
}
```

---

## How this checks your boxes

* **Single, reusable canonical DTO named `CampaignInboundDTO`** in your **provider code** (fits your tree and stays ergonomic).
* The DTO **implements your existing `InboundDTOContract`** and contains only **provider-agnostic fields**.
* The **Adapter is the only place** that “knows” Google Ads SDK shapes; it **maps** into the DTO and **namespaces** provider specifics under `attributes['google_ads.*']`.
* **Easy to test:** you can unit test the adapter by faking rows/results (simple stubs with `getX()` methods) and asserting on the DTO’s `toArray()` without any SDK imports in the DTO itself.
* **Future-proof:** when you add `Xero/.../InvoiceInboundDTO`, reuse the same DTO shape and write a Xero adapter that maps Xero fields to `externalId`, `label`, `state`, and namespaced attributes like `xero.invoices.*`.

If you share your `InboundDTOContract` method list and any stricter return types it expects, I can align the getters precisely—but the above should plug in cleanly for most contracts (`toArray()` + accessors).

