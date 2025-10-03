# Stripe CRUD Example: REST-Based CRUD Operations

This example demonstrates how the **CRUD Adapter pattern** works equally well with **REST APIs** (using Saloon) as it does with **vendor SDKs**.

## Key Insight

The `AdapterVerbContract` is **transport-agnostic**. It doesn't care whether you:
- Use a vendor SDK (like Google Ads PHP SDK)
- Make REST calls via Saloon
- Use GraphQL, gRPC, or any other protocol

## Architecture Comparison

### Google Ads (SDK-Based CRUD)
```
CampaignCrudAdapter
├── CampaignCreate (uses Google Ads SDK)
├── CampaignUpdate (uses Google Ads SDK)
├── CampaignDelete (uses Google Ads SDK)
├── CampaignRead (uses Google Ads SDK)
└── CampaignReadMany (uses Google Ads SDK)
```

### Stripe (REST-Based CRUD)
```
CustomerCrudAdapter
├── CustomerCreate (uses Saloon)
├── CustomerUpdate (uses Saloon)
├── CustomerDelete (uses Saloon)
└── CustomerRead (uses Saloon)
```

## Both Implement AdapterVerbContract

```php
interface AdapterVerbContract
{
    public static function inputSpec(): array;
    public function perform(CanonicalDTOContract $dto, bool $validateOnly): Result;
    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly): mixed;
    public function responseMapper(mixed $response, mixed $dto): CanonicalDTOContract;
}
```

## REST Implementation Details

### 1. Create Operation (POST /v1/customers)

```php
// requestMapper returns an anonymous Saloon Request class
return new class($dto) extends Request implements HasBody
{
    use HasFormBody; // Stripe uses form-encoded bodies
    
    protected Method $method = Method::POST;
    
    public function resolveEndpoint(): string
    {
        return '/v1/customers';
    }
    
    protected function defaultBody(): array
    {
        return [
            'email' => $this->dto->email,
            'name' => $this->dto->name,
        ];
    }
};

// Then send via connector
$response = $this->connector->send($request);
```

### 2. Update Operation (POST /v1/customers/:id)

Note: **Stripe uses POST for updates**, not PUT/PATCH! This is a quirk of their API.

```php
protected Method $method = Method::POST; // Not PUT!

public function resolveEndpoint(): string
{
    return '/v1/customers/' . $this->dto->externalId;
}
```

### 3. Read Operation (GET /v1/customers/:id)

```php
protected Method $method = Method::GET;

public function resolveEndpoint(): string
{
    return '/v1/customers/' . $this->dto->externalId;
}
```

### 4. Delete Operation (DELETE /v1/customers/:id)

```php
protected Method $method = Method::DELETE;

public function resolveEndpoint(): string
{
    return '/v1/customers/' . $this->dto->externalId;
}
```

## Usage Example

```php
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\Adapter\CustomerCrudAdapter;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\DTO\CustomerCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\Selector\CustomerSelector;

class CustomerService
{
    public function __construct(private CustomerCrudAdapter $adapter) {}
    
    public function createCustomer(string $email, string $name): Result
    {
        $dto = CustomerCanonicalDTO::fromArray([
            'email' => $email,
            'name' => $name,
        ]);
        
        return $this->adapter->create($dto);
    }
    
    public function updateCustomer(string $id, ?string $name = null): Result
    {
        $dto = CustomerCanonicalDTO::fromArray([
            'externalId' => $id,
            'name' => $name,
        ]);
        
        return $this->adapter->update($dto);
    }
    
    public function getCustomer(string $id): Result
    {
        $selector = CustomerSelector::byExternalId($id);
        return $this->adapter->find($selector);
    }
    
    public function deleteCustomer(string $id): Result
    {
        $selector = CustomerSelector::byExternalId($id);
        return $this->adapter->delete($selector);
    }
}
```

## When to Use Each Pattern

| Pattern | Best For | Example Use Cases |
|---------|----------|-------------------|
| **CRUD (SDK)** | Resources with clear lifecycle, vendor SDK available | Google Ads Campaigns, Facebook Ad Sets |
| **CRUD (REST)** | Resources with clear lifecycle, REST API only | Stripe Customers, Shopify Products, GitHub Repos |
| **Procedure** | Ad-hoc operations, rapid prototyping | Sending emails, quick searches, scripts |
| **REST** | Complex type-safe operations, many endpoints | OpenAI completions, complex queries |

## Benefits of REST-Based CRUD

✅ **Same familiar pattern** - Create, Read, Update, Delete operations  
✅ **Predictable structure** - Each verb is its own class  
✅ **Type-safe** - Uses Saloon Request classes  
✅ **Testable** - Mock the connector, test operations independently  
✅ **Provider-agnostic DTO** - Same `CustomerCanonicalDTO` regardless of transport  
✅ **Validation built-in** - `INPUT_SPEC` validates before API calls  

## Stripe API Quirks Handled

1. **Form-encoded bodies** - Uses `HasFormBody` trait instead of JSON
2. **POST for updates** - Stripe uses POST, not PUT/PATCH
3. **Nested error structure** - Errors are in `response['error']['message']`
4. **Delete response** - Returns `{id, deleted: true}` instead of 204

## Other Good REST CRUD Examples

### GitHub Repositories
- `POST /repos/:owner/:repo` (create)
- `GET /repos/:owner/:repo` (read)
- `PATCH /repos/:owner/:repo` (update)
- `DELETE /repos/:owner/:repo` (delete)

### Shopify Products
- `POST /admin/api/2024-01/products.json` (create)
- `GET /admin/api/2024-01/products/:id.json` (read)
- `PUT /admin/api/2024-01/products/:id.json` (update)
- `DELETE /admin/api/2024-01/products/:id.json` (delete)

### Twilio Messages
- `POST /2010-04-01/Accounts/:sid/Messages.json` (create)
- `GET /2010-04-01/Accounts/:sid/Messages/:sid.json` (read)
- `POST /2010-04-01/Accounts/:sid/Messages/:sid.json` (update)
- `DELETE /2010-04-01/Accounts/:sid/Messages/:sid.json` (delete)
