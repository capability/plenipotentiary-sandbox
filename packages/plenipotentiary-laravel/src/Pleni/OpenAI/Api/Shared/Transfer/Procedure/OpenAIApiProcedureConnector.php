<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Shared\Transfer\Procedure;

use Plenipotentiary\Laravel\Pleni\OpenAI\Api\Shared\Support\OpenAIConfig;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * Saloon connector for OpenAI API Procedure/RPC API.
 *
 * Configures the base URL, authentication, and default headers
 * for RPC-style communication with OpenAI API endpoints.
 */
final class OpenAIApiProcedureConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return OpenAIConfig::baseUrl();
    }

    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.OpenAIConfig::apiKey(),
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
