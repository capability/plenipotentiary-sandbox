<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Support\InputSource;

use Illuminate\Support\Arr;

final class ArraySource implements InputSource
{
    /** @param array<string,mixed> $data */
    public function __construct(private array $data) {}

    public function get(string $key): mixed
    {
        return Arr::get($this->data, $key);
    }
}
