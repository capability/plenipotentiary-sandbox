<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\CrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\CrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter\TodoCreate;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter\TodoCrudAdapter;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter\TodoDelete;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter\TodoList;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter\TodoRead;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter\TodoUpdate;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Gateway\TodoCrudGateway;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Support\TodoIdempotencyHints;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Shared\Client\JSONPlaceholderAPIClient;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Shared\Support\JSONPlaceholderErrorMapper;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Shared\Transfer\Rest\JSONPlaceholderAPIRestConnector;

final class TodoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Connector and Client
        $this->app->singleton(JSONPlaceholderAPIRestConnector::class);
        $this->app->singleton(ProviderClientContract::class, JSONPlaceholderAPIClient::class);

        // Operations
        $this->app->singleton(TodoCreate::class);
        $this->app->singleton(TodoRead::class);
        $this->app->singleton(TodoUpdate::class);
        $this->app->singleton(TodoDelete::class);
        $this->app->singleton(TodoList::class);

        // Adapter / Gateway
        $this->app->singleton(CrudAdapterContract::class, TodoCrudAdapter::class);
        $this->app->singleton(CrudGatewayContract::class, TodoCrudGateway::class);

        // Error Mapper
        $this->app->singleton(ErrorMapperContract::class, JSONPlaceholderErrorMapper::class);

        // Idempotency Hints
        $this->app->when(TodoCrudGateway::class)
            ->needs(IdempotencyHints::class)
            ->give(TodoIdempotencyHints::class);
    }
}
