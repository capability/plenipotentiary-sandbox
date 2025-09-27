<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Mapper;

use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;

interface OutboundMapperContract
{
    /**
     * Map an OutboundDTOContract into an array payload (ready for API).
     */
    public function map(OutboundDTOContract $dto): array;
}
