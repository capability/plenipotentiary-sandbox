---
sidebar_position: 3
title: Developer Workflow
---

# Developer Workflow - CRUD SDK Example

Learn the real API first. Codify your understanding. Let tooling follow your spec.

## The Six Steps

### Step 1: Start with the Real API

**Principle:** Learn the provider SDK/API first. No premature abstraction.

Start with the provider's SDK documentation in **one file**. Copy their working example, make a real API call, and see what comes back. *Understand the API* before building any abstractions.

```php
// CampaignCreate.php - Start here, all in one place
// Based on Google's AddCampaigns.php example (see CreateCampaignExample.php in repo root)

public function performWithArray(array $input): Result
{
    // Development helper - takes raw array, explores the API
    // Google SDK example: lines 126-149 from AddCampaigns.php

    $campaign = new Campaign([
        'name' => $input['name'],
        'advertising_channel_type' => AdvertisingChannelType::SEARCH,
        'status' => CampaignStatus::PAUSED, // Start paused
        'manual_cpc' => new ManualCpc(),
        'campaign_budget' => $input['budgetResourceName'], // From budget creation
    ]);

    $operation = new CampaignOperation();
    $operation->setCreate($campaign);

    // Google SDK: lines 153-156 from AddCampaigns.php
    $response = $this->client
        ->getCampaignServiceClient()
        ->mutateCampaigns(
            MutateCampaignsRequest::build(
                $input['customerId'],
                [$operation]
            )
        );

    // See what comes back - understand the structure!
    return Result::ok(['resourceName' => $response->getResults()[0]->getResourceName()]);
}
```

**Our Approach:** You understand the API call flow

**Magic Universal Wrapper Mistakes:** Starting with abstractions before understanding the API

### Step 2: Define Your INPUT_SPEC

**Principle:** Codify the minimum data you need. This is YOUR contract.

Write down what **your application needs**, not everything the API offers. Define it as `INPUT_SPEC` with Laravel validation rules. This becomes your contract—clear, auditable, and shareable.

```php
// CampaignCreate.php
// YOUR contract - what YOUR domain needs, not everything Google supports
public const INPUT_SPEC = [
    'name' => [
        'rules' => ['required', 'string', 'min:1', 'max:128'],
    ],
    'status' => [
        'rules' => ['nullable', 'in:ENABLED,PAUSED,REMOVED'],
    ],
    'budgetMicros' => [
        'rules' => ['nullable', 'numeric', 'min:0'],
    ],
    'budgetResourceName' => [
        'rules' => ['nullable', 'string'],
    ],
    // customerId comes from providerContext - auto-injected from env
    'providerContext.google.customerId' => [
        'rules' => ['required', 'string'],
        'source' => 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID',
    ],
];

public static function inputSpec(): array
{
    return self::INPUT_SPEC;
}
```

**Our Approach:** Explicit, auditable contract visible in code

**Magic Universal Wrapper Mistakes:** Hidden validation, magic field mappings, "the framework handles it"

### Step 3: Test Until Green

**Principle:** Write requestMapper() and responseMapper() until tests pass.

Build request and response mappers in the same file. Write tests for **success, validation errors, and API failures**. Keep iterating until all tests pass. Green tests mean you understand the integration.

```php
// CampaignCreate.php
private function requestMapper(array $validated): Campaign
{
    return new Campaign([
        'name' => $validated['name'],
        'advertisingChannelType' => AdvertisingChannelType::SEARCH,
        'status' => CampaignStatus::value($validated['status']),
        'campaignBudget' => $this->budgetResourceName(
            $validated['budget']
        ),
    ]);
}

private function responseMapper(MutateCampaignsResponse $res): array
{
    $result = $res->getResults()[0];
    return [
        'id' => basename($result->getResourceName()),
        'resourceName' => $result->getResourceName(),
    ];
}

// CampaignCreateTest.php - MUST be green!
test('creates campaign with valid input', function() {
    $result = $operation->perform([
        'name' => 'Test Campaign',
        'budget' => 50000,
    ]);

    expect($result->isOk())->toBeTrue();
});
```

**Our Approach:** Operation tested, API understood, confidence gained

**Magic Universal Wrapper Mistakes:** Moving to gateway before understanding the operation

### Step 4: Call Through Gateway

**Principle:** Gateway reveals the DTO contract based on YOUR INPUT_SPEC.

Call your operation through the Gateway. It will **show you exactly what DTO to create**, derived from your INPUT_SPEC. No guessing—the structure comes from what you already defined.

```php
// From your Action/Controller/Job
$dto = CampaignCanonicalDTO::fromArray([]);
$result = $gateway->create($dto);

// Remote API rejected the request
// - name (required): Required
// - providerContext.google.customerId (required): Required
//
// Expected DTO shape:
{
    "dto": {
        "fields": {
            "name": {
                "required": true,
                "rules": ["required", "string", "min:1", "max:128"],
                "type": "string"
            },
            "status": {
                "required": false,
                "rules": ["nullable", "in:ENABLED,PAUSED,REMOVED"],
                "type": "enum"
            },
            "budgetMicros": {
                "required": false,
                "rules": ["nullable", "numeric", "min:0"],
                "type": "numeric"
            },
            "budgetResourceName": {
                "required": false,
                "rules": ["nullable", "string"],
                "type": "string"
            }
        },
        "providerContext": {
            "google.customerId": {
                "required": true,
                "rules": ["required", "string"],
                "source": "env:GOOGLE_ADS_LINKED_CUSTOMER_ID",
                "type": "string"
            }
        }
    }
}

// Gateway normalizes everything to Result
interface Result {
    isOk(): bool;
    isErr(): bool;
    value(): mixed;  // Your domain data
    error(): array;  // Normalized errors
}
```

**Our Approach:** Gateway boundary defined, contract visible

**Magic Universal Wrapper Mistakes:** Premature DTO design before knowing what the API needs

### Step 5: Scaffold to Your Spec

**Principle:** Generate DTO/Factory from the INPUT_SPEC you wrote.

Generate the DTO and Factory that the Gateway showed you. They're **based on your INPUT_SPEC**—the contract you defined while learning the API. Tooling follows your understanding, not the other way around.

```php
// CampaignCanonicalDTO.php - Generated from YOUR spec
final class CampaignCanonicalDTO implements CanonicalDTOContract
{
    /** @var array<string,string> */
    public array $providerContext = [];

    public ?string $internalId = null;

    public ?string $externalId = null;

    public ?string $name = null;

    public ?string $status = null;

    public ?string $budgetResourceName = null;

    public ?int $budgetMicros = null;

    public static function fromArray(array $data): self
    {
        $dto = new self;
        $dto->providerContext = self::filterContext($data['providerContext'] ?? $data['accountKeys'] ?? []);
        $dto->internalId = $data['internalId'] ?? null;
        $dto->externalId = $data['externalId'] ?? null;
        $dto->name = $data['name'] ?? null;
        $dto->status = $data['status'] ?? null;
        $dto->budgetResourceName = $data['budgetResourceName'] ?? null;
        $dto->budgetMicros = isset($data['budgetMicros']) ? (int) $data['budgetMicros'] : null;

        return $dto;
    }
}

// CampaignCanonicalFactory.php
final class CampaignCanonicalFactory
{
    public function make(array $input): CampaignCanonicalDTO
    {
        // Gateway validates against INPUT_SPEC before this runs
        return CampaignCanonicalDTO::fromArray($input);
    }
}
```

**Our Approach:** Type-safe contracts scaffolded from your understanding

**Magic Universal Wrapper Mistakes:** Auto-generated DTOs that include fields you don't need

### Step 6: Robustness Comes Online

**Principle:** Cross-cutting concerns layer on top automatically.

With the Gateway boundary established, robustness features **activate automatically**: validation, error mapping, idempotency, queueing, and logging. They weren't in your way while learning—now they protect your production code.

```php
// Gateway validates DTO against INPUT_SPEC BEFORE calling adapter
// If validation fails, adapter never runs
$result = $gateway->create($dto);

// Provider rejected our data (Google's validation, not ours)
if ($result->isInvalid()) {
    // Google Ads rejected the campaign (budget too low, invalid name, etc.)
    $rawResponse = $result->rawResponse(); // Check Google's actual error
    return response()->json([
        'message' => 'Provider rejected data',
        'violations' => $result->violations()
    ], 422);
}

// Provider error (network, API limit, auth failure, etc.)
if ($result->isErr()) {
    return $result->error(); // Normalized error structure
}

// Success: Get canonical DTO AND raw provider response
if ($result->isOk()) {
    $campaign = $result->unwrap(); // CampaignCanonicalDTO
    $rawResponse = $result->rawResponse(); // MutateCampaignsResponse

    Log::info('Campaign created', [
        'externalId' => $campaign->externalId,
        'resourceName' => $rawResponse->getResults()[0]->getResourceName()
    ]);
}

// Idempotency (automatic via policy)
$result = $gateway->create($dto, idempotencyKey: 'campaign-' . $dto->name);

// Queueing (Laravel integration)
dispatch(new CreateCampaignJob($dto));
```

**Our Approach:** Production-ready with idempotency, validation, queueing, logging

**Magic Universal Wrapper Mistakes:** Adding cross-cutting concerns while still learning the API

## Two Approaches Compared

### Plenipotentiary - API-First Approach

1. **Copy real SDK example** - Start with provider documentation
2. **Make real API call** - See actual responses
3. **Define INPUT_SPEC from learning** - Codify what you discovered
4. **Test until green** - Validate your understanding
5. **Gateway shows required DTO** - Structure emerges from spec
6. **Scaffold from YOUR spec** - Generate only what you need
7. **Robustness layers on** - Add cross-cutting concerns last

**Result: Understanding → Contract → Tooling**

You know exactly what the API does and why your code works.

### Historic API Wrapper - Try to Make Life "Easier" Approach

1. **Design perfect abstract DTOs** - Before knowing the API
2. **Build universal mapper** - Trying to handle all cases
3. **Add all possible fields** - "Just in case" mentality
4. **Hide validation in framework** - Magic config files
5. **Abstract before understanding** - Layers upon layers
6. **Debug magic when it breaks** - Where is this error from?
7. **Never really understand the API** - Perpetual confusion

**Result: Abstraction → Confusion → Debugging**

You're constantly fighting the framework and never sure why things break.

## Key Insight

Most frameworks hide the API behind abstractions, promising to make integration "easier." **Plenipotentiary forces you to learn the API first**, then provides structure for what you learned. The result? You understand your integrations completely, and when something breaks, you know exactly where to look.
