<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Support\InputSource;

interface InputSource
{
    public function get(string $key): mixed;
}
