<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Transfer\Procedure;

use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support\EbayConfig;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * Saloon connector for eBay Browse Procedure/RPC API.
 *
 * Configures the base URL, authentication, and default headers
 * for RPC-style communication with eBay Browse endpoints.
 */
final class eBayBrowseProcedureConnector extends Connector
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
            'Authorization' => 'Bearer '.$this->config->accessToken(),
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
