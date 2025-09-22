<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\DTO;

/**
 * Marker interface for outbound (domain → external) DTOs.
 */
interface OutboundDTOContract
{
    /**
     * Serialize the outbound DTO to an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array;
}
