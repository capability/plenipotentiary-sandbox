<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Shared\Support;

/**
 * Centralised configuration loader for OpenAI.
 * All env() lookups should be done here.
 */
final class OpenAIConfig
{
    public static function apiKey(): string
    {
        return (string) env('OPENAI_API_KEY', '');
    }

    public static function organizationId(): ?string
    {
        $org = env('OPENAI_ORGANIZATION_ID', '');

        return $org !== '' ? $org : null;
    }

    public static function baseUrl(): string
    {
        return (string) env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
    }
}
