Here’s a drop-in spec you can save as `docs/aider-spec.md`. It explains the new approach, matches your namespaces and current tree, and gives aider exact targets and signatures to work against.

---

# Pleni Google Ads, Campaign adapter, Option B spec

Goal, canonical DTO in, provider request out, minimal local validation, provider validate only supported, unified mutate used when creating budget on the fly.

## Directory and namespaces

Root under `Plenipotentiary\Laravel\Pleni\Google\Ads`

```
Pleni/
  Google/Ads/
    Contexts/Search/Campaign/
      Adapter/
        CampaignApiCrudAdapter.php
        Create/
          Spec.php
          RequestMapper.php
          ResponseMapper.php
          Budget/RequestMapper.php
        Read/
          LookupRequestMapper.php
          LookupResponseMapper.php
        Update/
        Delete/
      DTO/
        CampaignCanonicalDTO.php
      Gateway/
        CampaignApiCrudGateway.php
      Key/
        CampaignSelector.php
        CampaignSelectorKind.php
      Repository/
        CampaignRepositoryContract.php
        EloquentCampaignRepository.php

    Shared/
      Auth/
        GoogleAdsSdkAuthStrategy.php
        GoogleAdsSdkClient.php
      Lookup/
        Op.php
        Dir.php
        Criterion.php
        Sort.php
        Lookup.php
        Page.php
        Gaql/QueryBuilder.php
      Providers/
        GoogleAdsServiceProvider.php
      Support/
        GoogleAdsErrorMapper.php
        GoogleAdsHelper.php
```

Support types already exist

```
Support/Result.php
Support/Operation/{OperationDescription.php, ValidationException.php}
```

## Canonical DTO

`Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO`

Fields, all nullable unless stated

```php
public string  $customerId;           // required for all calls
public ?string $resourceName = null;  // read result, delete key variant
public ?string $id = null;            // provider id
public ?string $name = null;
public ?string $status = null;        // ENABLED, PAUSED
public ?string $budgetResourceName = null;
public ?int    $cpcBidMicros = null;
public ?int    $budgetMicros = null;  // optional, used when creating budget on the fly
```

## Contracts used

* `ProviderClientContract`, call `raw()` to get SDK client wrapper
* `ErrorMapperContract`, normalise exceptions to your error shape
* `Result`, ok or err or invalid

## Create flow

Two paths, single resource, unified mutate

* If `budgetResourceName` is present, build a `MutateCampaignsRequest` and call `CampaignService::mutateCampaigns`
* If `budgetResourceName` is missing, build a unified `MutateGoogleAdsRequest` with a `CampaignBudgetOperation` using a negative temp id and a `CampaignOperation` that references it, call `GoogleAdsService::mutate`

Validate only, flip the request flag, no writes

### Files

* `Create/Spec.php`, cheap preflight and `describe()`
* `Create/Budget/RequestMapper.php`, builds `CampaignBudgetOperation` from canonical
* `Create/RequestMapper.php`, chooses single vs unified, builds request
* `Create/ResponseMapper.php`, maps mutate response to canonical

### Signatures

```php
// Spec.php
public function preflight(CampaignCanonicalDTO $c): void;
public function describe(): OperationDescription;

// Budget/RequestMapper.php
public function toBudgetOperation(CampaignCanonicalDTO $c, int $tempId): \Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation;

// Create/RequestMapper.php
public function toCampaignsRequest(CampaignCanonicalDTO $c, bool $validateOnly): \Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;

public function toUnifiedRequest(
    CampaignCanonicalDTO $c,
    bool $validateOnly
): \Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsRequest;

// Create/ResponseMapper.php
public function toCanonical(\Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsResponse|\Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse $resp): CampaignCanonicalDTO;
```

### Adapter create

`CampaignApiCrudAdapter::create(CampaignCanonicalDTO $c, bool $validateOnly = false): Result`

Flow

1. `Spec->preflight($c)`
2. `$ga = $this->client->raw()`
3. If `$c->budgetResourceName` is empty, call `Create\RequestMapper->toUnifiedRequest`, else `toCampaignsRequest`
4. Call either `getGoogleAdsServiceClient()->mutate($unified)` or `getCampaignServiceClient()->mutateCampaigns($req)`
5. If `validateOnly` is true, return `Result::ok()` with no payload
6. Map with `Create\ResponseMapper`, return `Result::ok($canonical)`
7. Catch `ValidationException`, return `Result::invalid(...)`
8. Catch `\Throwable`, map with `GoogleAdsErrorMapper`, return `Result::err(...)`

## Read single

Input is a key, not the canonical DTO

* `Key/CampaignSelector.php`, `CampaignSelectorKind.php`
* Adapter builds a GAQL query from the selector and maps to canonical

### Files

* `Read/LookupRequestMapper.php`, uses Shared Lookup builder or direct selector
* `Read/LookupResponseMapper.php`

### Signatures

```php
public function toSelectorQuery(string $customerId, CampaignSelector $sel): string;
public function toCanonical(object $searchResponse): CampaignCanonicalDTO;
```

## Lookup many

Use Shared Lookup DSL

* `Shared/Lookup/{Lookup, Criterion, Op, Dir, Sort, Page}`
* `Shared/Lookup/Gaql/QueryBuilder` converts canonical fields to GAQL using a resource field map defined in `LookupRequestMapper`

### Files

* `Read/LookupRequestMapper.php` owns a whitelist map

```php
private const FIELD = [
  'resourceName'       => 'campaign.resource_name',
  'id'                 => 'campaign.id',
  'name'               => 'campaign.name',
  'status'             => 'campaign.status',
  'budgetResourceName' => 'campaign.campaign_budget',
];
```

### Signatures

```php
public function toQuery(string $customerId, \Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup $q): array;
// returns ['query' => string, 'pageToken' => ?string]

public function toPage(object $searchResponse): \Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Page;
```

## Delete

Input is `CampaignSelector`, build a `MutateCampaignsRequest` with `remove` op, execute

### Signatures

```php
public function toDeleteRequest(string $customerId, CampaignSelector $sel, bool $validateOnly): \Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
```

## Update

Keep it simple for now, patch semantics

* Track touched fields inside the mapper, or add a tiny trait later
* Build `CampaignOperation->setUpdate($campaign)` with a field mask derived from touched fields
* Use `mutateCampaigns`, support validate only

## Validation modes

Add a simple flag at call sites, the request mappers already take `bool $validateOnly`

* CLI and scaffolding, pass `true`
* Prod, pass `false`
* No hybrid two step, one call only

## Error handling

Always normalise to `Result`

* Local preflight, throw `ValidationException`, adapter returns `Result::invalid([...violations...])`
* Provider errors, catch `\Throwable`, hand to `GoogleAdsErrorMapper`, return `Result::err([...])`

## Aider tasks

Do now

* Create `Create/Budget/RequestMapper.php`, implement temp id pattern, negative id, default to `-1`
* Implement `Create/RequestMapper::toUnifiedRequest`, call budget mapper then wrap both operations in a `MutateGoogleAdsRequest`, set `validate_only` flag
* Implement `Create/RequestMapper::toCampaignsRequest`, honour `validate_only`
* Implement `CampaignApiCrudAdapter::create`, no `$customerId` parameter, use `$c->customerId`
* Ensure enums in Shared Lookup are unit enums, not backed, Op and Dir
* Rename DTO file to `CampaignCanonicalDTO.php` everywhere, keep that exact class name for now

Later

* Implement `Delete` and `Update` mappers
* Add `Update` field mask generation
* Add `Gateway/CampaignApiCrudGateway.php` calls for `lookup`, `read`, `delete`, `update`
* Contract tests for `create` with and without budget, validate only and execute

## Acceptance checks

* Given `CampaignCanonicalDTO` with `customerId`, `name`, `status`, `budgetMicros` only, `create($dto, validateOnly:true)` returns `Result::ok()`, no writes
* Same input with `validateOnly:false` returns `Result::ok(CampaignCanonicalDTO)` with `resourceName` and `id`
* Given `CampaignCanonicalDTO` with `budgetResourceName` preset, `create` uses `CampaignService::mutateCampaigns`, not unified mutate
* Read with `CampaignSelector::externalId('123')` returns canonical with matching `id`
* Lookup with `status IN [ENABLED, PAUSED]` and `name starts with 'Brand - '` builds GAQL with whitelisted columns only

That is the spec aider should follow.

