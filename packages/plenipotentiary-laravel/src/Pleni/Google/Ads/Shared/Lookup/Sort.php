<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup;

final class Sort
{
    public function __construct(
        public readonly string $field,   // canonical field
        public readonly Dir $dir = Dir::Asc
    ) {}
}
