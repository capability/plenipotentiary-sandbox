<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support;

/**
 * Centralised configuration for eBay Browse API.
 * 
 * This configuration class encapsulates all eBay-specific settings,
 * keeping them isolated from the rest of the application.
 */
final class EbayConfig
{
    public function __construct(
        private readonly string $accessToken,
        private readonly string $marketplaceId = 'EBAY_US',
        private readonly bool $isSandbox = false,
        private readonly ?string $clientId = null,
        private readonly ?string $clientSecret = null,
        private readonly ?string $refreshToken = null,
        private readonly ?string $redirectUri = null,
        private readonly ?string $endUserCtx = null,
    ) {}

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function marketplaceId(): string
    {
        return $this->marketplaceId;
    }

    public function isSandbox(): bool
    {
        return $this->isSandbox;
    }

    public function clientId(): ?string
    {
        return $this->clientId;
    }

    public function clientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function refreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function redirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function endUserCtx(): ?string
    {
        return $this->endUserCtx;
    }
}
