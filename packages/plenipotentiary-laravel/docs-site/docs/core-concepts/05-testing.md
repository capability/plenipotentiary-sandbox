---
sidebar_position: 5
title: Testing
---

# Testing

A comprehensive test harness that enables AI agents to confidently maintain adapters through mechanical API changes

## Why Comprehensive Testing?

**AI agents can reliably handle mechanical refactoring—but only with deterministic success signals.** When Google Ads renames a method or changes a field type, AI can update your adapter code **if tests immediately confirm the change worked**. Without comprehensive tests, AI can't distinguish between "mechanical change applied correctly" and "broke the integration."

### The AI Maintenance Promise

With full test coverage:
- ✅ AI detects API deprecation warnings in provider SDK
- ✅ AI updates adapter code (rename method, change field type)
- ✅ Tests run → all green → AI confirms success
- ✅ Tests run → failures → AI tries alternative approach
- ✅ Mechanical changes proven safe by deterministic tests

Without proper tests:
- ❌ AI changes code → no way to verify correctness
- ❌ You manually test in staging → defeats the purpose
- ❌ Break production → AI couldn't detect the issue

## Full Test Harness Structure

### CRUD Pattern Example: Google Ads Campaign

```
Pleni/Google/Ads/Contexts/Default/Campaign/
├── Tests/
│   ├── Unit/
│   │   ├── Adapter/
│   │   │   ├── CampaignCreateTest.php
│   │   │   ├── CampaignReadTest.php
│   │   │   ├── CampaignReadManyTest.php
│   │   │   ├── CampaignUpdateTest.php
│   │   │   └── CampaignDeleteTest.php
│   │   ├── DTO/
│   │   │   └── CampaignCanonicalDTOTest.php
│   │   └── Support/
│   │       ├── CampaignIdempotencyHintsTest.php
│   │       └── GoogleAdsErrorMapperTest.php
│   ├── Integration/
│   │   ├── CampaignCreateIntegrationTest.php
│   │   ├── CampaignReadIntegrationTest.php
│   │   ├── CampaignReadManyIntegrationTest.php
│   │   ├── CampaignUpdateIntegrationTest.php
│   │   └── CampaignDeleteIntegrationTest.php
│   └── Feature/
│       └── CampaignGatewayFeatureTest.php
```

## Test Types and Coverage

### 1. Unit Tests: Adapter Operations

**Purpose:** Test individual adapter operations in isolation with mocked SDK clients.

**File:** `Tests/Unit/Adapter/CampaignCreateTest.php`

```php
<?php

namespace Tests\Unit\Google\Ads\Campaign\Adapter;

use Tests\TestCase;
use Pleni\Google\Ads\Contexts\Default\Campaign\Adapter\CampaignCreate;
use Pleni\Google\Ads\Contexts\Default\Campaign\DTO\CampaignCanonicalDTO;
use Google\Ads\GoogleAds\V16\Services\CampaignServiceClient;
use Google\Ads\GoogleAds\V16\Services\MutateCampaignsResponse;
use Mockery;

class CampaignCreateTest extends TestCase
{
    /** @test */
    public function it_creates_campaign_with_valid_input()
    {
        // Arrange
        $mockClient = Mockery::mock(CampaignServiceClient::class);
        $mockResponse = Mockery::mock(MutateCampaignsResponse::class);
        $mockResponse->shouldReceive('getResults->getResourceName')
            ->andReturn('customers/123/campaigns/456');

        $mockClient->shouldReceive('mutateCampaigns')
            ->once()
            ->andReturn($mockResponse);

        $adapter = new CampaignCreate($mockClient);

        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test Campaign',
            'status' => 'PAUSED',
            'budgetMicros' => 50000,
        ]);

        // Act
        $result = $adapter->perform($dto);

        // Assert
        $this->assertTrue($result->isOk());
        $this->assertEquals('456', $result->unwrap()->externalId);
    }

    /** @test */
    public function it_fails_when_input_spec_validation_fails()
    {
        // INPUT_SPEC validation happens at Gateway layer
        // Adapter receives already-validated DTO
        // But we test adapter's own validation logic here

        $adapter = new CampaignCreate($this->mockClient());

        $dto = CampaignCanonicalDTO::fromArray([
            'name' => '', // Empty name should fail
        ]);

        $result = $adapter->perform($dto);

        $this->assertTrue($result->isInvalid());
        $this->assertArrayHasKey('name', $result->violations());
    }

    /** @test */
    public function it_handles_google_ads_api_errors()
    {
        // Arrange
        $mockClient = Mockery::mock(CampaignServiceClient::class);
        $mockClient->shouldReceive('mutateCampaigns')
            ->andThrow(new \Google\ApiCore\ApiException('RATE_LIMIT_EXCEEDED', 429));

        $adapter = new CampaignCreate($mockClient);
        $dto = CampaignCanonicalDTO::fromArray(['name' => 'Test']);

        // Act
        $result = $adapter->perform($dto);

        // Assert
        $this->assertTrue($result->isErr());
        $this->assertEquals('RATE_LIMIT_EXCEEDED', $result->error()['code']);
    }

    /** @test */
    public function it_maps_provider_response_to_canonical_dto()
    {
        // Test the responseMapper specifically
        $mockResponse = $this->createMockMutateCampaignsResponse([
            'resourceName' => 'customers/123/campaigns/789',
            'campaign' => [
                'id' => 789,
                'name' => 'Created Campaign',
                'status' => 'PAUSED',
            ],
        ]);

        $adapter = new CampaignCreate($this->mockClient());
        $dto = $adapter->mapResponseToDTO($mockResponse);

        $this->assertInstanceOf(CampaignCanonicalDTO::class, $dto);
        $this->assertEquals('789', $dto->externalId);
        $this->assertEquals('Created Campaign', $dto->name);
        $this->assertEquals('PAUSED', $dto->status);
    }

    /** @test */
    public function it_maps_request_dto_to_provider_format()
    {
        // Test the requestMapper specifically
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetMicros' => 100000,
        ]);

        $adapter = new CampaignCreate($this->mockClient());
        $campaign = $adapter->mapDTOToRequest($dto);

        $this->assertInstanceOf(\Google\Ads\GoogleAds\V16\Resources\Campaign::class, $campaign);
        $this->assertEquals('Test Campaign', $campaign->getName());
        $this->assertEquals(CampaignStatus::ENABLED, $campaign->getStatus());
    }
}
```

### 2. Unit Tests: DTO Validation

**Purpose:** Test DTO construction, validation, and factory methods.

**File:** `Tests/Unit/DTO/CampaignCanonicalDTOTest.php`

```php
<?php

namespace Tests\Unit\Google\Ads\Campaign\DTO;

use Tests\TestCase;
use Pleni\Google\Ads\Contexts\Default\Campaign\DTO\CampaignCanonicalDTO;

class CampaignCanonicalDTOTest extends TestCase
{
    /** @test */
    public function it_creates_dto_from_array_with_valid_data()
    {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetMicros' => 50000,
            'providerContext' => ['google' => ['customerId' => '123']],
        ]);

        $this->assertEquals('Test Campaign', $dto->name);
        $this->assertEquals('ENABLED', $dto->status);
        $this->assertEquals(50000, $dto->budgetMicros);
        $this->assertEquals('123', $dto->providerContext['google']['customerId']);
    }

    /** @test */
    public function it_handles_missing_optional_fields()
    {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test Campaign',
        ]);

        $this->assertEquals('Test Campaign', $dto->name);
        $this->assertNull($dto->status);
        $this->assertNull($dto->budgetMicros);
    }

    /** @test */
    public function it_validates_input_spec_rules()
    {
        $inputSpec = CampaignCanonicalDTO::inputSpec();

        // Name is required and max 128 characters
        $this->assertTrue(in_array('required', $inputSpec['name']['rules']));
        $this->assertTrue(in_array('max:128', $inputSpec['name']['rules']));

        // Status must be valid enum value
        $this->assertTrue(in_array('in:ENABLED,PAUSED,REMOVED', $inputSpec['status']['rules']));

        // Budget must be numeric and positive
        $this->assertTrue(in_array('numeric', $inputSpec['budgetMicros']['rules']));
        $this->assertTrue(in_array('min:0', $inputSpec['budgetMicros']['rules']));
    }

    /** @test */
    public function it_converts_to_array()
    {
        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'externalId' => '456',
        ]);

        $array = $dto->toArray();

        $this->assertEquals('Test Campaign', $array['name']);
        $this->assertEquals('ENABLED', $array['status']);
        $this->assertEquals('456', $array['externalId']);
    }
}
```

### 3. Unit Tests: Gateway Orchestration

**Purpose:** Test Gateway validates INPUT_SPEC, applies policies, and orchestrates adapter calls.

**File:** `Tests/Unit/Gateway/CampaignCrudGatewayTest.php`

```php
<?php

namespace Tests\Unit\Google\Ads\Campaign\Gateway;

use Tests\TestCase;
use Pleni\Google\Ads\Contexts\Default\Campaign\Gateway\CampaignCrudGateway;
use Pleni\Google\Ads\Contexts\Default\Campaign\Adapter\CampaignCreate;
use Pleni\Google\Ads\Contexts\Default\Campaign\DTO\CampaignCanonicalDTO;
use Mockery;

class CampaignCrudGatewayTest extends TestCase
{
    /** @test */
    public function it_validates_input_before_calling_adapter()
    {
        $mockAdapter = Mockery::mock(CampaignCreate::class);
        $mockAdapter->shouldNotReceive('perform'); // Should never reach adapter

        $gateway = new CampaignCrudGateway($mockAdapter);

        $dto = CampaignCanonicalDTO::fromArray([
            'name' => '', // Invalid: empty name
        ]);

        $result = $gateway->create($dto);

        $this->assertTrue($result->isInvalid());
        $this->assertArrayHasKey('name', $result->violations());
    }

    /** @test */
    public function it_applies_idempotency_policy()
    {
        $mockAdapter = Mockery::mock(CampaignCreate::class);
        $mockAdapter->shouldReceive('perform')
            ->once() // Should only be called once despite duplicate
            ->andReturn(Result::ok(new CampaignCanonicalDTO()));

        $gateway = new CampaignCrudGateway($mockAdapter);
        $dto = CampaignCanonicalDTO::fromArray(['name' => 'Test']);

        // First call
        $result1 = $gateway->create($dto, idempotencyKey: 'campaign-123');

        // Second call with same key - should return cached result
        $result2 = $gateway->create($dto, idempotencyKey: 'campaign-123');

        $this->assertTrue($result1->isOk());
        $this->assertTrue($result2->isOk());
    }

    /** @test */
    public function it_applies_error_mapping_policy()
    {
        $mockAdapter = Mockery::mock(CampaignCreate::class);
        $mockAdapter->shouldReceive('perform')
            ->andThrow(new \Google\ApiCore\ApiException('INVALID_ARGUMENT', 400));

        $gateway = new CampaignCrudGateway($mockAdapter);
        $dto = CampaignCanonicalDTO::fromArray(['name' => 'Test']);

        $result = $gateway->create($dto);

        // Gateway should map Google SDK exception to domain error
        $this->assertTrue($result->isErr());
        $this->assertEquals('VALIDATION_ERROR', $result->error()['code']);
    }

    /** @test */
    public function it_applies_rate_limiting_policy()
    {
        $mockAdapter = Mockery::mock(CampaignCreate::class);
        $mockAdapter->shouldReceive('perform')
            ->times(5) // Rate limit: 5 per minute
            ->andReturn(Result::ok(new CampaignCanonicalDTO()));

        $gateway = new CampaignCrudGateway($mockAdapter);
        $dto = CampaignCanonicalDTO::fromArray(['name' => 'Test']);

        // Make 6 requests rapidly
        for ($i = 0; $i < 6; $i++) {
            $result = $gateway->create($dto);

            if ($i < 5) {
                $this->assertTrue($result->isOk());
            } else {
                // 6th request should be rate limited
                $this->assertTrue($result->isErr());
                $this->assertEquals('RATE_LIMIT_EXCEEDED', $result->error()['code']);
            }
        }
    }
}
```

### 4. Integration Tests: End-to-End Flows

**Purpose:** Test complete flow from Gateway through Adapter to mocked provider API.

**File:** `Tests/Integration/CampaignCreateIntegrationTest.php`

```php
<?php

namespace Tests\Integration\Google\Ads\Campaign;

use Tests\TestCase;
use Pleni\Google\Ads\Contexts\Default\Campaign\Gateway\CampaignCrudGateway;
use Pleni\Google\Ads\Contexts\Default\Campaign\DTO\CampaignCanonicalDTO;

class CampaignCreateIntegrationTest extends TestCase
{
    /** @test */
    public function it_creates_campaign_end_to_end()
    {
        // Uses test double for Google Ads client (not mocked at unit level)
        $gateway = app(CampaignCrudGateway::class);

        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Integration Test Campaign',
            'status' => 'PAUSED',
            'budgetMicros' => 50000,
            'providerContext' => [
                'google' => ['customerId' => config('google.test_customer_id')],
            ],
        ]);

        $result = $gateway->create($dto);

        $this->assertTrue($result->isOk());

        $campaign = $result->unwrap();
        $this->assertNotNull($campaign->externalId);
        $this->assertEquals('Integration Test Campaign', $campaign->name);
        $this->assertEquals('PAUSED', $campaign->status);

        // Verify raw response is available
        $rawResponse = $result->rawResponse();
        $this->assertInstanceOf(MutateCampaignsResponse::class, $rawResponse);
    }

    /** @test */
    public function it_handles_duplicate_campaign_name()
    {
        $gateway = app(CampaignCrudGateway::class);

        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Duplicate Name Test',
        ]);

        // Create first campaign
        $result1 = $gateway->create($dto);
        $this->assertTrue($result1->isOk());

        // Try to create duplicate - Google Ads may reject
        $result2 = $gateway->create($dto);

        if ($result2->isInvalid()) {
            $this->assertArrayHasKey('name', $result2->violations());
        }
    }

    /** @test */
    public function it_validates_budget_constraints()
    {
        $gateway = app(CampaignCrudGateway::class);

        $dto = CampaignCanonicalDTO::fromArray([
            'name' => 'Low Budget Test',
            'budgetMicros' => 100, // Too low for Google Ads
        ]);

        $result = $gateway->create($dto);

        $this->assertTrue($result->isInvalid());
        $this->assertArrayHasKey('budgetMicros', $result->violations());
    }
}
```

### 5. Feature Tests: Application-Level Integration

**Purpose:** Test from controller/action perspective, verify complete request lifecycle.

**File:** `Tests/Feature/CampaignGatewayFeatureTest.php`

```php
<?php

namespace Tests\Feature\Google\Ads\Campaign;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pleni\Google\Ads\Contexts\Default\Campaign\Gateway\CampaignCrudGateway;

class CampaignGatewayFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function controller_can_create_campaign_via_gateway()
    {
        $response = $this->postJson('/api/campaigns', [
            'name' => 'Feature Test Campaign',
            'status' => 'ENABLED',
            'budget' => 50000,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'name' => 'Feature Test Campaign',
            'status' => 'ENABLED',
        ]);
    }

    /** @test */
    public function controller_returns_422_on_validation_failure()
    {
        $response = $this->postJson('/api/campaigns', [
            'name' => '', // Empty name
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function controller_returns_422_on_provider_rejection()
    {
        // Mock gateway to return provider validation error
        $this->mock(CampaignCrudGateway::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andReturn(Result::invalid(['name' => 'Campaign name already exists']));
        });

        $response = $this->postJson('/api/campaigns', [
            'name' => 'Existing Campaign',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Provider rejected data',
            'violations' => ['name' => 'Campaign name already exists'],
        ]);
    }

    /** @test */
    public function job_can_create_campaign_via_gateway()
    {
        $job = new CreateCampaignJob([
            'name' => 'Job Test Campaign',
        ]);

        $job->handle(app(CampaignCrudGateway::class));

        // Verify job completed successfully
        $this->assertTrue($job->wasSuccessful());
    }

    /** @test */
    public function action_can_create_campaign_via_gateway()
    {
        $result = CreateCampaignAction::run([
            'name' => 'Action Test Campaign',
        ]);

        $this->assertTrue($result->isOk());
        $this->assertNotNull($result->unwrap()->externalId);
    }
}
```

## Test Coverage Requirements

### Minimum Coverage for AI Maintenance

For AI agents to confidently maintain adapters, each operation must have:

**1. Unit Tests (Adapter Level)**
- ✅ Happy path: Valid input → successful API call → correct DTO mapping
- ✅ Validation failures: INPUT_SPEC violations → isInvalid() result
- ✅ Provider errors: API exceptions → isErr() result with domain error
- ✅ Provider rejection: Data rejected by provider → isInvalid() with violations
- ✅ Request mapping: DTO → provider format (verify field mappings)
- ✅ Response mapping: Provider response → canonical DTO (verify field mappings)

**2. Integration Tests**
- ✅ End-to-end flow: Gateway → Adapter → Test double API
- ✅ Edge cases: Empty results, null values, boundary conditions
- ✅ Cross-cutting concerns: Idempotency, rate limiting, error mapping

**3. Feature Tests**
- ✅ Controller integration
- ✅ Job integration
- ✅ Action integration

### Coverage Metrics

```bash
# Minimum coverage thresholds
php artisan test --coverage --min=80

# Per-class coverage
- Adapter operations: 90%+
- DTOs: 95%+
- Gateway: 85%+
- Integration: 80%+
```

## Mocking Strategies

### 1. Unit Tests: Mock SDK Clients

```php
// Mock Google Ads SDK client
$mockClient = Mockery::mock(CampaignServiceClient::class);
$mockClient->shouldReceive('mutateCampaigns')
    ->once()
    ->with(Mockery::type(MutateCampaignsRequest::class))
    ->andReturn($mockResponse);
```

### 2. Integration Tests: Use Test Doubles

```php
// Bind test double in service container
$this->app->bind(GoogleAdsClient::class, function () {
    return new GoogleAdsTestDouble([
        'testMode' => true,
        'recordResponses' => true,
    ]);
});
```

### 3. Feature Tests: Mock Gateway

```php
// Mock gateway for controller tests
$this->mock(CampaignCrudGateway::class, function ($mock) {
    $mock->shouldReceive('create')
        ->andReturn(Result::ok($this->createTestCampaign()));
});
```

## AI Agent Workflow

### How AI Uses Tests to Maintain Adapters

**Scenario:** Google Ads SDK v16 → v17 migration renames `CampaignStatus` enum values

**AI Process:**

1. **Detect API Change**
   - Read Google Ads v17 changelog
   - Identify: `PAUSED` → `CAMPAIGN_PAUSED`

2. **Update Adapter Code**
   ```php
   // Old code
   'status' => CampaignStatus::PAUSED

   // AI updates to
   'status' => CampaignStatus::CAMPAIGN_PAUSED
   ```

3. **Run Tests**
   ```bash
   php artisan test Tests/Unit/Adapter/CampaignCreateTest.php
   ```

4. **Interpret Results**
   - ✅ All tests pass → Change confirmed correct
   - ❌ Tests fail → AI tries alternative approach
   - ❌ Tests fail → AI requests human review

5. **Update Test Fixtures**
   - AI updates test expectations to match v17 API
   - Re-runs tests → Confirms full migration

### What Makes This Reliable

**Deterministic success signals:**
- Test passes = Code works
- Test fails = Code broken
- No ambiguity, no manual verification needed

**Mechanical changes only:**
- Renaming methods ✅
- Changing field types ✅
- Updating enum values ✅
- Business logic changes ❌ (requires human)

**AI boundaries:**
- AI can maintain mechanical changes
- AI cannot make business decisions (which campaigns to pause?)
- AI cannot design new features
- AI excels at keeping existing code working as APIs evolve

## Scaffolding Test Generation

### Future: Auto-Generate Test Harness

```bash
php artisan pleni:make:crud \
  --provider=Google \
  --domain=Ads \
  --resource=Campaign \
  --with-tests  # Generates full test harness
```

**Generated tests:**
- Unit tests for each adapter operation (Create, Read, Update, Delete)
- DTO validation tests
- Gateway orchestration tests
- Integration tests with test doubles
- Feature tests for Laravel integration
- Example fixtures and mocks

**AI maintenance enabled from day one.**

## Best Practices

### 1. Test Names Are Documentation

```php
// ✅ Good: Describes what and why
public function it_maps_google_ads_campaign_status_to_canonical_dto_status()

// ❌ Bad: Generic, unclear
public function test_status()
```

### 2. Test One Behavior Per Test

```php
// ✅ Good: Single assertion, clear failure
public function it_validates_campaign_name_is_required()
{
    $result = $adapter->perform(CampaignCanonicalDTO::fromArray(['name' => '']));
    $this->assertTrue($result->isInvalid());
}

// ❌ Bad: Multiple behaviors, unclear which failed
public function it_validates_input()
{
    // Tests name, status, budget all together
}
```

### 3. Use Descriptive Assertions

```php
// ✅ Good: Clear what's being validated
$this->assertEquals('PAUSED', $dto->status);
$this->assertInstanceOf(CampaignCanonicalDTO::class, $result->unwrap());

// ❌ Bad: Unclear expectations
$this->assertTrue($dto->status == 'PAUSED');
```

### 4. Test Boundary Conditions

```php
public function it_handles_empty_campaign_list()
public function it_handles_null_budget()
public function it_handles_max_length_campaign_name()
public function it_handles_special_characters_in_name()
```

### 5. Test Cross-Cutting Concerns

```php
public function it_applies_idempotency_on_duplicate_create()
public function it_maps_rate_limit_error_to_domain_exception()
public function it_logs_campaign_creation_attempt()
public function it_caches_campaign_read_result()
```

## Summary

**For AI agents to maintain adapters through mechanical API changes, you need:**

1. **Comprehensive test coverage** - Every operation, every edge case
2. **Deterministic success signals** - Tests pass = code works
3. **Clear test structure** - Unit, Integration, Feature separation
4. **Good mocking** - Fast, reliable, no external dependencies
5. **Descriptive test names** - AI and humans understand intent

**With this test harness:**
- ✅ AI can safely apply mechanical SDK updates
- ✅ AI can verify changes immediately
- ✅ You review test results, not code line-by-line
- ✅ Confidence in automated maintenance

**Without proper tests:**
- ❌ AI can't verify changes worked
- ❌ You manually test in staging
- ❌ Risk breaking production
- ❌ No automation possible
