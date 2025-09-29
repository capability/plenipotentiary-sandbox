<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Gateway;

use Plenipotentiary\Laravel\Support\Result;

/**
 * Gateway contract for flexible API endpoint operations.
 *
 * Provides a single "escape hatch" to any provider endpoint,
 * maintaining the Gateway/Adapter pattern for non-CRUD APIs.
 */
interface ApiEndpointGatewayContract
{
    /**
     * Execute any API call with full flexibility
     *
     * @param  string  $operation  The operation to perform (e.g., 'searchItems', 'createCompletion')
     * @param  array  $payload  Request data to send
     * @param  array  $options  Additional options (headers, URL params, etc.)
     */
    public function call(
        string $operation,
        array $payload = [],
        array $options = []
    ): Result;

    /**
     * Validate-only calls for testing
     *
     * @param  string  $operation  The operation to validate
     * @param  array  $payload  Request data to validate
     */
    public function validate(
        string $operation,
        array $payload = []
    ): Result;
}
