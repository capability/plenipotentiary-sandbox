Here’s a revised **AGENTS.md** that reflects your points: auth is provider-level (not in operations), failures are structure-driven (expected shape first, then violations), and DTOs/Selectors support a `providerContext` for provider-specific identifiers with env-driven defaults that auto-hydrate.

---

# AGENTS.md

## 🧭 Adapter-First, Spec-Led

The **adapter operation** (e.g., `Adapter/CreateOperation.php`) is where understanding is earned. You start with the provider **SDK** in a single file, make the call work, and from that knowledge declare a minimal **`INPUT_SPEC`**. That spec drives the generated **CanonicalDTO** and **Factory**—not the other way around.

Plenipotentiary promotes **understanding over abstraction**: you write the SDK call and the spec; the tooling scaffolds from what you proved works.

---

## 🔐 Provider-Level Auth (shared, not in operations)

* Authentication is configured **once per provider** using a package **AuthStrategy** (e.g., `GoogleAdsSdkAuthStrategy`) and wired in the provider’s Service Provider.
* Operations receive an **authed client via DI**; they **do not** implement auth themselves.
* For tests, **mock the client** behind the same contract; for dev, use real creds (sandbox/`validateOnly` when available).

---

## 🧱 `INPUT_SPEC` — Simple, Specifying Enough

`INPUT_SPEC` must be **as simple as possible**, while carrying enough info to:

1. build the CanonicalDTO + Factory shape and
2. validate/preflight inputs.

### Design rules

* **Canonical property names** as keys (what the DTO exposes).
* **Validation**: minimal rules (Laravel-style or internal equivalent).
* **Casting hints**: small, deterministic set (e.g., `currency_to_micros`).
* **Dotted keys for `providerContext`** — use `providerContext.*` to declare provider-specific identifiers the adapter will need (e.g., `providerContext.google.customerId`).
* **Optional `source` hints** (purely informative): `env:GOOGLE_ADS_LINKED_CUSTOMER_ID`. The **Factory automatically applies provider defaults**; you **do not** call defaults manually in commands/actions.

**Example (inside `Adapter/CreateOperation.php`):**

```php
public const INPUT_SPEC = [
    'name'         => ['rules' => ['required','string','min:1']],
    'status'       => ['rules' => ['required','in:ENABLED,PAUSED,REMOVED']],
    'budgetMicros' => ['rules' => ['required','numeric','min:0'], 'cast' => 'currency_to_micros'],
    'customerId'   => ['rules' => ['required','string']],

    // Provider-specific IDs / names live under providerContext.*
    'providerContext.google.customerId'   => ['rules' => ['required','string'], 'source' => 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID'],
    'providerContext.google.resourceName' => ['rules' => ['nullable','string']], // read/update/delete
];
```

> Keep it flat with dotted keys; the hydrator builds nested structures in the DTO (`providerContext` array) automatically.

---

## 🧩 DTOs & Selectors: `providerContext` and Defaults

* **DTOs and Selectors** include a `providerContext: array` to carry **provider-specific identifiers** (e.g., Google’s `resource_name`) alongside canonical fields (`externalId`, etc.).
* **Do not** manually apply defaults in entrypoints. The **Factory** auto-hydrates from `{Provider}{Service}Defaults` (e.g., `GoogleAdsDefaults`) which reads environment values and injects them **before** validation.
* If a required `providerContext` key is not present after defaults + input merge, validation will fail like any other missing field.

**Example DTO shape (conceptual):**

```php
final class CampaignCanonicalDTO {
    public function __construct(
        public ?string $externalId = null,
        public ?string $name = null,
        public ?string $status = null,
        public ?int    $budgetMicros = null,
        public ?string $customerId = null,
        public array   $providerContext = [] // e.g., ['google.customerId' => '...', 'google.resourceName' => '...']
    ) {}
}
```

---

## ❌ Failure Payload — Structure First, Then Violations

When preflighting/validating with a DTO that doesn’t fit, return a **structured failure**:

1. **`expected`** — the expected DTO/Selector structure derived from `INPUT_SPEC` (including required fields, types/casts, and providerContext keys).
2. **`violations`** — explicit, well-formed list of what’s wrong.

**Example:**

```json
{
  "expected": {
    "dto": {
      "fields": {
        "name":         {"required": true,  "type": "string"},
        "status":       {"required": true,  "type": "enum", "values": ["ENABLED","PAUSED","REMOVED"]},
        "budgetMicros": {"required": true,  "type": "int",   "cast": "currency_to_micros"},
        "customerId":   {"required": true,  "type": "string"}
      },
      "providerContext": {
        "google.customerId":   {"required": true,  "type": "string", "source": "env:GOOGLE_ADS_LINKED_CUSTOMER_ID"},
        "google.resourceName": {"required": false, "type": "string"}
      }
    }
  },
  "violations": [
    {"field":"name","message":"Required"},
    {"field":"providerContext.google.customerId","message":"Required (set GOOGLE_ADS_LINKED_CUSTOMER_ID or provide explicitly)."}
  ]
}
```

This format is **machine-friendly** (for tooling) and **developer-friendly** (clear next steps).

---

## 🔁 Operation Structure (one file, tests drive “green”)

Keep everything in the operation file until tests are green:

* **SDK call** (built from the DTO)
* **Preflight** derived from `INPUT_SPEC`
* **Request build → SDK invoke → response map**
* **Throw provider exceptions**; **Gateway** will map to domain (`invalid` vs `err`) via the provider’s `ErrorMapper`.

Unit tests for `perform()` must cover **success**, **invalid input** (with structured failures as above), and **mapped provider errors**.

---

## 🎯 Your Role as Agent

### Primary Responsibilities

1. **Spec-driven operations**: Ensure each `*Operation.php` declares a minimal `INPUT_SPEC` that truly reflects the SDK call and the business use case.
2. **Structured failures**: Implement preflight that returns `expected` + `violations` as shown above—no opaque errors.
3. **DTO/Factory readiness**: Keep `INPUT_SPEC` simple and deterministic so DTO + Factory can be generated directly (dotted keys → nested structures; known casts; optional `source` hints).
4. **Provider auth is shared**: Confirm operations receive an authed client via provider-level strategy; don’t embed auth in operations.
5. **Provider context**: Use `providerContext.*` for provider-specific identifiers; rely on defaults auto-hydration (no manual defaults in entrypoints).
6. **Testing**: Provide unit tests for `perform()` (ok/invalid/err) and happy E2E through the Gateway.

### Key Principles

* **Understanding over abstraction**: Start with the SDK in one file; encode what you learned in `INPUT_SPEC`.
* **Provider semantics**: Preserve natural operation names and patterns.
* **Contract-driven**: Implementations satisfy **core package** contracts (contracts live centrally).
* **Scaffolding-ready**: Code is template-quality for generation.

### Current Focus Areas

1. **INPUT_SPEC completeness** (incl. `providerContext.*` where needed).
2. **Exemplary adapters**: Google Ads/eBay/OpenAI reflect the pattern crisply.
3. **Gateway mapping**: Provider exceptions → domain results via `ErrorMapper`.
4. **Test coverage**: Green unit tests on operations and Gateway pass-throughs.
5. **Docs**: Concise, copy-pasteable examples for spec, failures, and defaults.

---

## 🚀 Example Usage Patterns

**CRUD (create)**

```php
$result = $campaignGateway->create($campaignDto);
if ($result->isOk()) {
    $campaign = $result->unwrap();
} elseif ($result->isInvalid()) {
    // show $result->violations() (you also have $result->toArray()['expected'])
}
```

**Consistent error handling**

```php
if ($result->isInvalid()) {
    $violations = $result->violations();
} elseif ($result->isErr()) {
    $error = $result->error(); // { code, message, retryable, http, meta }
}
```

---

## 📋 Development Guidelines

* **Contracts in core**; providers implement them.
* **Operation-first**: Build the SDK call, then declare `INPUT_SPEC`.
* **Structured preflight**: Failures must include `expected` + `violations`.
* **Auto-defaults**: `{Provider}{Service}Defaults` auto-hydrate; no manual defaults application in entrypoints.
* **Comprehensive tests**: success / invalid (with structured failure) / mapped errors.
* **Template quality**: keep operations clean and copy-worthy.
* **Docs**: short, accurate examples for spec, providerContext, and defaults.

---

✅ **Context Complete**: Make adapter operations the source of truth—SDK call → `INPUT_SPEC` → structured preflight → predictable results. From that, DTO + Factory are scaffolded to your spec, and the Gateway remains a stable, provider-agnostic, toolable boundary.
