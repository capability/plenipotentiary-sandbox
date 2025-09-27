<?php

namespace Plenipotentiary\Laravel\Pleni\Support;

final class Page
{
    /** @param array<int,mixed> $items */
    public function __construct(
        public readonly array $items,
        public readonly ?string $nextCursor = null
    ) {}
}
