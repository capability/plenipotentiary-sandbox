<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;
use Psr\Http\Message\RequestInterface;

final class NoopAuthStrategy implements AuthStrategyContract
{
    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        return $request;
    }
}
