<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Support\OpenAIConfig;
use Psr\Http\Message\RequestInterface;

/**
 * OpenAI SDK Authentication Strategy
 *
 * Handles API key authentication for OpenAI API.
 * Uses simple bearer token authentication with OpenAI API key.
 */
final class OpenAISdkAuthStrategy implements SdkAuthStrategyContract
{
    private string $apiKey;

    private string $organizationId;

    public function __construct()
    {
        $this->apiKey = OpenAIConfig::apiKey();
        $this->organizationId = OpenAIConfig::organizationId();

        if (empty($this->apiKey)) {
            throw new \RuntimeException('OpenAI API key not configured. Please set OPENAI_API_KEY environment variable.');
        }
    }

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        // For SDK-based clients, authentication is handled internally
        // This method is required by the contract but not used for SDK auth
        return $request;
    }

    /**
     * Get the authenticated OpenAI HTTP client configuration
     */
    public function getClient(): OpenAIClientConfig
    {
        return new OpenAIClientConfig($this->apiKey, $this->organizationId);
    }

    /**
     * Get the raw API key for direct use
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get the organization ID
     */
    public function getOrganizationId(): ?string
    {
        return $this->organizationId ?: null;
    }
}

/**
 * OpenAI Client Configuration
 *
 * Simple configuration object to hold OpenAI API credentials
 * and provide methods for building authenticated requests.
 */
final class OpenAIClientConfig
{
    public function __construct(
        private string $apiKey,
        private ?string $organizationId = null
    ) {}

    /**
     * Get the API key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get the organization ID
     */
    public function getOrganizationId(): ?string
    {
        return $this->organizationId;
    }

    /**
     * Get headers for OpenAI API requests
     */
    public function getHeaders(): array
    {
        $headers = [
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($this->organizationId) {
            $headers['OpenAI-Organization'] = $this->organizationId;
        }

        return $headers;
    }

    /**
     * Get base URL for OpenAI API
     */
    public function getBaseUrl(): string
    {
        return OpenAIConfig::baseUrl();
    }
}
