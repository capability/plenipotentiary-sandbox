<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\DTO;

/**
 * Contextual, enriched inbound DTO contract.
 * Provides provider metadata, attributes, warnings in addition to the minimal fields.
 */
interface ContextualInboundDTOContract extends InboundDTOContract
{
    public function getOperation(): string;
    public function getMeta(): array;
    public function getAttributes(): array;
    public function getWarnings(): array;
}
