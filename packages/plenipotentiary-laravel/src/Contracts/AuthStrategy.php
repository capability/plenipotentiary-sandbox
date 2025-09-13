<?php

namespace Plenipotentiary\Laravel\Contracts;

use Psr\Http\Message\RequestInterface;

/**
 * AuthStrategy
 *
 * Applies authentication to an outbound PSR-7 request in a pure manner:
 * returns a new/modified Request without side effects.
 */
interface AuthStrategy
{
    /**
     * @param  RequestInterface  $request  The outbound request to sign or decorate.
     * @param  array<string,mixed>  $context  Optional context (scopes, tenant, provider hints).
     * @return RequestInterface The request with auth applied.
     */
    public function apply(RequestInterface $request, array $context = []): RequestInterface;
}
