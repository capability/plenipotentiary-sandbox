<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Transfer\Rest;

use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support\EbayConfig;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * Saloon connector for eBay Browse REST API.
 * 
 * Configures the base URL, authentication, and default headers
 * for communicating with eBay Browse REST endpoints.
 */
final class eBayBrowseRestConnector extends Connector
{
    use AcceptsJson;

    public function __construct(private EbayConfig $config) {}

    public function resolveBaseUrl(): string
    {
        return $this->config->isSandbox()
            ? 'https://api.sandbox.ebay.com'
            : 'https://api.ebay.com';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->config->accessToken(),
            'Content-Type' => 'application/json',
            'X-EBAY-C-MARKETPLACE-ID' => $this->config->marketplaceId(),
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => 30,
        ];
    }
}
