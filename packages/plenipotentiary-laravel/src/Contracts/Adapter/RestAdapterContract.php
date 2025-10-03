<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Support\Result;
use Saloon\Http\Request;

/**
 * Provider-specific adapter for REST operations using Saloon.
 *
 * This adapter uses saloonphp/saloon to communicate with third-party APIs.
 * Each provider domain implements this contract to provide REST-based communication.
 */
interface RestAdapterContract
{
    /**
     * Execute a Saloon request and return a Result.
     *
     * @param  Request  $request  The Saloon request object representing the API call
     * @return Result The result of the API call
     */
    public function execute(Request $request): Result;
}
