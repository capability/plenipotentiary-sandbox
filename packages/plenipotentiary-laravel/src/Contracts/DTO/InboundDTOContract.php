<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\DTO;

/**
 * Marker interface for inbound (external → domain) DTOs.
 */
interface InboundDTOContract
{
    /**
     * Hydrate an inbound DTO from raw array data.
     *
     * @param array<string,mixed> $data
     * @return static
     */
    public static function fromArray(array $data): self;
}
