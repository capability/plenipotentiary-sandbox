<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Transfer\Procedure;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsConfig;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * Saloon connector for Google Ads Procedure/RPC API.
 * 
 * Configures the base URL, authentication, and default headers
 * for RPC-style communication with Google Ads endpoints.
 */
final class GoogleAdsProcedureConnector extends Connector
{
    use AcceptsJson;

    public function __construct(private GoogleAdsConfig $config) {}

    public function resolveBaseUrl(): string
    {
        return 'https://googleads.googleapis.com/v16';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->config->accessToken(),
            'developer-token' => $this->config->developerToken(),
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => 30,
        ];
    }
}
