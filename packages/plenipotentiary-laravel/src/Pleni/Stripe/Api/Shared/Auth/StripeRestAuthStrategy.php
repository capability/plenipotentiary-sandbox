<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;
use Psr\Http\Message\RequestInterface;

/**
 * REST-based authentication strategy for Stripe API.
 *
 * Unlike SDK-based auth (which hands off to the SDK), REST auth
 * directly manipulates HTTP headers. Stripe uses simple Bearer token
 * authentication with the secret key.
 *
 * This can be used with:
 * - Saloon connectors (via middleware)
 * - PSR-7 HTTP clients (via apply())
 * - Any HTTP transport that accepts PSR-7 requests
 */
final class StripeRestAuthStrategy implements AuthStrategyContract
{
    public function __construct(
        private string $secretKey,
    ) {}

    /**
     * Apply Stripe authentication to an HTTP request.
     *
     * Stripe uses HTTP Basic Auth with the secret key as username
     * and empty password. This is equivalent to:
     * Authorization: Bearer sk_test_xxx (but Stripe wants Basic)
     */
    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        // Stripe uses HTTP Basic Auth: encode "secret_key:" as base64
        $credentials = base64_encode($this->secretKey.':');

        return $request->withHeader('Authorization', 'Basic '.$credentials);
    }

    /**
     * Get the secret key (useful for Saloon connectors).
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }
}
