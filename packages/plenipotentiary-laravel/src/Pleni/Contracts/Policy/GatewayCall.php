<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Contracts\Policy;

final class GatewayCall
{
    public function __construct(
        public readonly string $operation,
        public readonly array $payload = [],
        public readonly array $options = [],
        public readonly array $context = [],
        public readonly ?object $hints = null
    ) {}
}
