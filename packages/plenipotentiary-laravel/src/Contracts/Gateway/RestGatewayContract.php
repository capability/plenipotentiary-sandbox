<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Gateway;

use Plenipotentiary\Laravel\Support\Result;
use Saloon\Http\Request;

/**
 * Stable, predictable facade for REST-style API operations.
 * 
 * This gateway provides the entry point for REST operations, applying
 * cross-cutting concerns (logging, idempotency, error handling) before
 * delegating to the provider-specific RestAdapterContract.
 */
interface RestGatewayContract
{
    /**
     * Execute a Saloon request through the gateway.
     * 
     * @param Request $request The Saloon request object representing the API call
     * @return Result The result of the API call
     */
    public function execute(Request $request): Result;
}
