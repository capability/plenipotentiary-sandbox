<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Transfer\Rest;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsConfig;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * Saloon connector for Google Ads REST API.
 *
 * Configures the base URL, authentication, and default headers
 * for communicating with Google Ads REST endpoints.
 */
final class GoogleAdsRestConnector extends Connector
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
            'Authorization' => 'Bearer '.$this->config->accessToken(),
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
