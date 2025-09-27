<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Auth;

use Psr\Http\Message\RequestInterface;

/**
 * Root authentication strategy for HTTP-based providers.
 *
 * SDK-based strategies should instead implement SdkAuthStrategyContract.
 */
interface AuthStrategyContract
{
    /**
     * Apply authentication to an HTTP request (for API-based providers).
     */
    public function apply(RequestInterface $request, array $context = []): RequestInterface;
}
