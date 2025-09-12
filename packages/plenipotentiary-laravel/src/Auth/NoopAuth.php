<?php

namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\AuthStrategy;
use Psr\Http\Message\RequestInterface;

/**
 * NoopAuth
 *
 * A trivial strategy used as the default seam for initial tests.
 * Returns the request unchanged.
 */
class NoopAuth implements AuthStrategy
{
    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        return $request;
    }
}
