# Plenipotentiary Agent Architecture

This document outlines the architectural patterns for interacting with external provider APIs through this package.

## Core Principles

The primary goal of this package is to provide a stable, predictable, and reliable interface for communicating with external APIs. This allows for the implementation of common cross-cutting concerns such as:

-   **Idempotency**: Preventing duplicate operations.
-   **Queuing**: Safely dispatching API calls to background jobs.
-   **Logging & Monitoring**: Centralized and standardized logging for all provider interactions.
-   **Error Handling**: Consistent and predictable error mapping across all providers.

## 1. The CRUD Pattern

For APIs that expose resources with a clear Create, Read, Update, and Delete lifecycle (e.g., Google Ads Campaigns), we use the `ApiCrudGatewayContract`. This provides a structured and provider-agnostic way to manage these resources.

-   **Gateway**: `ApiCrudGatewayContract` - The public entry point.
-   **Adapter**: `ApiCrudAdapterContract` - The provider-specific implementation.

## 2. Non-CRUD Operations: A Dual-Choice Architecture

For all other API interactions that do not fit the CRUD model, we recognize that a single pattern is insufficient. Therefore, this package provides two distinct, first-class architectural patterns. Developers should choose the pattern that best fits their use case.

### 2.1. The RPC (Remote Procedure Call) Pattern

This pattern prioritizes **developer velocity and simplicity**. It is ideal for rapid prototyping, simple APIs, or ad-hoc scripts where creating dedicated classes for each endpoint feels like overkill.

To ensure stability for queuing and tooling, the entire RPC call is encapsulated into a single, serializable object.

**Contracts:**
- `RpcGatewayContract`: The public gateway, which accepts an `RpcOperationContract`.
- `RpcAdapterContract`: The provider-specific adapter, which still uses a `call(string, array, array)` signature internally.

**How It Works:**
The developer creates a simple `GenericRpcOperation` object, which represents a complete instruction. The `RpcGateway` receives this object, applies cross-cutting concerns (logging, idempotency), and then unpacks it to call the `RpcAdapter`.

#### **Example Usage:**

```php
use Plenipotentiary\Laravel\Contracts\Gateway\RpcGatewayContract;
use Plenipotentiary\Laravel\Support\Operation\GenericRpcOperation;

class EBaySearchAction
{
    public function __construct(private RpcGatewayContract $gateway) {}

    public function handle(string $query): Result
    {
        // 1. Create a stable, serializable operation object.
        $operation = new GenericRpcOperation(
            'searchItems', // The operation name the adapter will handle
            ['q' => $query, 'limit' => 10]
        );

        // 2. Execute the operation via the gateway.
        // This call is now safe to be queued.
        return $this->gateway->execute($operation);
    }
}
```

**Benefits:**
- **Fast Prototyping**: Quickly add support for new endpoints by modifying only the adapter's internal `match` statement.
- **Safe for Queuing**: The entire operation is a single, type-hinted object, making it reliable for background jobs.
- **Low Boilerplate**: No need to create new classes for each endpoint.

### 2.2. The REST/Saloon Pattern

This pattern prioritizes **architectural purity, long-term maintainability, and type safety**. It is the recommended choice for complex, critical, or frequently used APIs. It leverages the excellent `saloonphp/saloon` library to create a robust and scalable implementation.

**Contracts:**
- `RestGatewayContract`: The public gateway, which accepts a `Saloon\Http\Request` object.
- `RestAdapterContract`: The provider-specific adapter, which uses a Saloon `Connector` to send the request.

**How It Works:**
The developer creates a dedicated, self-contained `Saloon\Http\Request` class for each API endpoint. This class defines the HTTP method, path, and request data structure. The adapter becomes a simple pass-through, eliminating the "god `match` statement" anti-pattern.

#### **Example Usage:**

First, define a Saloon Request class for the endpoint:
```php
// packages/plenipotentiary-laravel/src/Pleni/eBay/Browse/Requests/SearchItemsRequest.php
use Saloon\Enums\Method;
use Saloon\Http\Request;

final class SearchItemsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/buy/browse/v1/item_summary/search';
    }

    public function __construct(public readonly string $query) {}

    protected function defaultQuery(): array
    {
        return ['q' => $this->query];
    }
}
```

Then, use it in your application:
```php
use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Requests\SearchItemsRequest;

class EBaySearchAction
{
    public function __construct(private RestGatewayContract $gateway) {}

    public function handle(string $query): Result
    {
        // 1. Create a type-safe, self-documenting request object.
        $request = new SearchItemsRequest($query);

        // 2. Execute the request via the gateway.
        return $this->gateway->execute($request);
    }
}
```

**Benefits:**
- **Scalable & Maintainable**: Follows the Open/Closed Principle. Adding a new endpoint requires creating a new file, not modifying an existing one.
- **Type-Safe**: The request's parameters are defined by the constructor, providing compile-time safety.
- **Self-Documenting**: The `Requests` directory becomes a clear, browsable list of all supported API operations.

By offering both the **RPC** and **REST/Saloon** patterns, this package empowers developers to make the right architectural trade-off for their specific context.