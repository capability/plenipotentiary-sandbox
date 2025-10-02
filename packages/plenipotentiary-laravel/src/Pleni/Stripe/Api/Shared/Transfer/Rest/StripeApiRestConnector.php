<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Transfer\Rest;

use Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Auth\StripeRestAuthStrategy;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Support\StripeConfig;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\HasTimeout;

/**
 * Saloon connector for Stripe REST API.
 * 
 * Authentication is handled via StripeRestAuthStrategy, which can be:
 * - Injected via constructor (for testing/flexibility)
 * - Auto-created from config (for production use)
 */
final class StripeApiRestConnector extends Connector
{
    use HasTimeout;

    public function __construct(
        private ?StripeRestAuthStrategy $authStrategy = null,
    ) {}

    public function resolveBaseUrl(): string
    {
        return 'https://api.stripe.com';
    }

    protected function defaultHeaders(): array
    {
        $authStrategy = $this->authStrategy ?? new StripeRestAuthStrategy(
            StripeConfig::secretKey()
        );

        return [
            // Stripe uses HTTP Basic Auth: secret_key as username
            'Authorization' => 'Basic ' . base64_encode($authStrategy->getSecretKey() . ':'),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Stripe-Version' => StripeConfig::apiVersion(),
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => 30,
        ];
    }

    /**
     * Boot the connector - set up timeout.
     */
    public function boot(): void
    {
        $this->timeout(30);
    }
}
