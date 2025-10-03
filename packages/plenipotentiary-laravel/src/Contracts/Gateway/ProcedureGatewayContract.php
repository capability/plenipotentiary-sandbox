<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Gateway;

use Plenipotentiary\Laravel\Support\Result;

/**
 * Stable, predictable facade for RPC/Procedure-style API operations.
 *
 * This gateway provides the entry point for procedure/RPC operations, applying
 * cross-cutting concerns (logging, idempotency, error handling) before
 * delegating to the provider-specific ProcedureAdapterContract.
 *
 * Use this pattern for rapid prototyping and simple APIs where creating
 * dedicated request classes feels like overkill.
 */
interface ProcedureGatewayContract
{
    /**
     * Execute an RPC-style operation through the gateway.
     *
     * @param  string  $operation  The operation name (e.g., 'searchItems', 'chat.completions.create')
     * @param  array  $payload  Request data to send
     * @param  array  $options  Additional options (headers, query params, etc.)
     * @return Result The result of the API call
     */
    public function call(string $operation, array $payload = [], array $options = []): Result;

    /**
     * Validate an operation without executing it.
     *
     * @param  string  $operation  The operation to validate
     * @param  array  $payload  Request data to validate
     * @return Result The validation result
     */
    public function validate(string $operation, array $payload = []): Result;
}
