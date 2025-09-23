<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;
use Psr\Http\Message\RequestInterface;

/**
 * Simple static/bearer token authentication strategy.
 *
 * Attaches a header (default "Authorization") with "Bearer <token>"
 * or a custom prefix + value. The token may be supplied directly
 * via constructor or dynamically through the request context.
 */
final class TokenAuthStrategy implements AuthStrategyContract
{
    /**
     * @param string $header Header name to use, defaults to "Authorization"
     * @param string $prefix Prefix before token value, defaults to "Bearer "
     * @param string $value  Optional default token value
     */
    public function __construct(
        private string $header = 'Authorization',
        private string $prefix = 'Bearer ',
        private string $value  = ''
    ) {}

    /**
     * Apply token authentication header to the request.
     *
     * @param RequestInterface $request
     * @param array $context May contain 'token' key to override default
     * @return RequestInterface
     */
    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $token = $context['token'] ?? $this->value;
        if ($token === '') {
            return $request;
        }
        return $request->withHeader($this->header, $this->prefix.$token);
    }
}
