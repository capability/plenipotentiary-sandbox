<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Idempotency;

/**
 * Defines how to generate idempotency fingerprints for arbitrary endpoint calls.
 */
interface EndpointIdempotencyHints
{
    /**
     * Compute a deterministic fingerprint for an endpoint operation.
     *
     * Implementations should consider the operation name and the
     * canonicalized payload/options that influence side effects.
     */
    public function fingerprintForCall(string $operation, array $payload = [], array $options = []): string;
}