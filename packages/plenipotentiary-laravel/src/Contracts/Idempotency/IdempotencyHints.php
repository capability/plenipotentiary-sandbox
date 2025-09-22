<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Idempotency;

use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;

/**
 * Adapters can optionally implement this to provide idempotency hints.
 */
interface IdempotencyHints
{
    public function inferExternalReference(OutboundDTOContract $dto): ?string;

    public function fingerprintForCreate(OutboundDTOContract $dto): string;

    public function fingerprintForUpdate(string $externalRef, OutboundDTOContract $dto): string;

    public function fingerprintForDelete(string $externalRef): string;
}
