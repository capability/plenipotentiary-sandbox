<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Support\InputSource;

use Illuminate\Http\Request;

final class RequestSource implements InputSource
{
    public function __construct(private Request $request) {}

    public function get(string $key): mixed
    {
        return $this->request->input($key);
    }
}
