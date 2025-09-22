<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Mapper;

use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;

interface InboundMapperContract
{
    /**
     * Map raw payload (array) into an InboundDTOContract.
     *
     * @param array<string,mixed> $payload
     */
    public function map(array $payload): InboundDTOContract;
}
