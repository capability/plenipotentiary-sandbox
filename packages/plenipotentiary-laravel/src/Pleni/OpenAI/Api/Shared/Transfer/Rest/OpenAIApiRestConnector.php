<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Shared\Transfer\Rest;

use Plenipotentiary\Laravel\Pleni\OpenAI\Api\Shared\Support\OpenAIConfig;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * Saloon connector for OpenAI API REST API.
 * 
 * Configures the base URL, authentication, and default headers
 * for communicating with OpenAI API REST endpoints.
 */
final class OpenAIApiRestConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return OpenAIConfig::baseUrl();
    }

    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . OpenAIConfig::apiKey(),
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => 60, // OpenAI can take longer for completions
        ];
    }
}
