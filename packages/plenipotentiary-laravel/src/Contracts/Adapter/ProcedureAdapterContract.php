<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Support\Result;

/**
 * Provider-specific adapter for RPC/Procedure-style operations using Saloon.
 * 
 * This adapter provides a simple, flexible interface for calling any API endpoint
 * without creating dedicated request classes. It uses saloonphp/saloon for HTTP
 * communication, making it ideal for rapid prototyping and ad-hoc API calls.
 */
interface ProcedureAdapterContract
{
    /**
     * Execute an RPC-style operation.
     * 
     * @param string $operation The operation name (e.g., 'searchItems', 'chat.completions.create')
     * @param array $payload Request data to send
     * @param array $options Additional options (headers, query params, etc.)
     * @return Result The result of the API call
     */
    public function call(string $operation, array $payload = [], array $options = []): Result;

    /**
     * Validate an operation without executing it.
     * 
     * @param string $operation The operation to validate
     * @param array $payload Request data to validate
     * @return Result The validation result
     */
    public function validate(string $operation, array $payload = []): Result;
}
