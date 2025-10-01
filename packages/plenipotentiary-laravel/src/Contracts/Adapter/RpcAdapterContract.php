<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Support\Result;

/**
 * Adapter contract for flexible API endpoint operations.
 *
 * Provider-specific implementation of endpoint calls,
 * handles the actual API communication and response mapping.
 */
interface RpcAdapterContract
{
    /**
     * Provider-specific implementation of endpoint calls
     *
     * @param  string  $operation  The operation to perform
     * @param  array  $payload  Request data to send
     * @param  array  $options  Additional options (headers, URL params, etc.)
     */
    public function call(
        string $operation,
        array $payload = [],
        array $options = []
    ): Result;

    /**
     * Provider-specific validation
     *
     * @param  string  $operation  The operation to validate
     * @param  array  $payload  Request data to validate
     */
    public function validate(
        string $operation,
        array $payload = []
    ): Result;
}
