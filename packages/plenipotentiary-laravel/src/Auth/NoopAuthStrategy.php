<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;
use Psr\Http\Message\RequestInterface;

/**
 * No-op authentication strategy.
 *
 * This strategy deliberately does nothing to the request,
 * useful for public APIs or testing scenarios where no authentication
 * is required.
 */
final class NoopAuthStrategy implements AuthStrategyContract
{
    /**
     * Simply return the incoming request unmodified.
     *
     * @param RequestInterface $request
     * @param array $context
     * @return RequestInterface
     */
    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        return $request;
    }
}
