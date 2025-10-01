<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Auth;

use Bricre\EbaySdkBuyBrowse\Configuration;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Psr\Http\Message\RequestInterface;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support\EbayConfig;

/**
 * eBay Browse SDK Authentication Strategy
 *
 * Handles OAuth 2.0 authentication for eBay Browse API.
 * Manages access token generation and refresh using eBay's OAuth flow.
 */
final class eBaySdkAuthStrategy implements SdkAuthStrategyContract
{
    private Configuration $config;

    private ?string $accessToken = null;

    private ?int $tokenExpiresAt = null;

    public function __construct()
    {
        $this->config = Configuration::getDefaultConfiguration();
        $this->initializeAuthentication();
    }

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        // For SDK-based clients, authentication is handled internally
        // This method is required by the contract but not used for SDK auth
        return $request;
    }

    /**
     * Get the authenticated eBay Browse SDK Configuration
     */
    public function getClient(): Configuration
    {
        // Ensure we have a valid access token
        if ($this->shouldRefreshToken()) {
            $this->refreshAccessToken();
        }

        return $this->config->setAccessToken($this->accessToken);
    }

    /**
     * Get the raw access token for direct use
     */
    public function getAccessToken(): string
    {
        if ($this->shouldRefreshToken()) {
            $this->refreshAccessToken();
        }

        return $this->accessToken;
    }

    /**
     * Initialize authentication using environment variables
     */
    private function initializeAuthentication(): void
    {
        $clientId = EbayConfig::clientId();
        $clientSecret = EbayConfig::clientSecret();
        $refreshToken = EbayConfig::refreshToken();
        $redirectUri = EbayConfig::redirectUri();

        if (! $clientId || ! $clientSecret) {
            throw new \RuntimeException('eBay client credentials not configured. Please set EBAY_CLIENT_ID and EBAY_CLIENT_SECRET environment variables.');
        }

        // Set up OAuth configuration
        $this->config->setClientId($clientId);
        $this->config->setClientSecret($clientSecret);

        if ($redirectUri) {
            $this->config->setRedirectUri($redirectUri);
        }

        // If we have a refresh token, use it to get an access token
        if ($refreshToken) {
            $this->accessToken = $this->obtainAccessTokenFromRefreshToken($refreshToken);
        } else {
            // For applications that need user consent, you might need to handle the OAuth flow
            throw new \RuntimeException('eBay refresh token not configured. Please set EBAY_REFRESH_TOKEN environment variable or implement OAuth flow.');
        }
    }

    /**
     * Obtain access token using refresh token
     */
    private function obtainAccessTokenFromRefreshToken(string $refreshToken): string
    {
        $clientId = EbayConfig::clientId();
        $clientSecret = EbayConfig::clientSecret();

        $credentials = base64_encode($clientId.':'.$clientSecret);

        $response = $this->makeTokenRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ], $credentials);

        $this->tokenExpiresAt = time() + ($response['expires_in'] ?? 7200);

        return $response['access_token'];
    }

    /**
     * Check if we need to refresh the access token
     */
    private function shouldRefreshToken(): bool
    {
        if (! $this->accessToken || ! $this->tokenExpiresAt) {
            return true;
        }

        // Refresh token 5 minutes before expiry
        return time() >= ($this->tokenExpiresAt - 300);
    }

    /**
     * Refresh the access token
     */
    private function refreshAccessToken(): void
    {
        $refreshToken = EbayConfig::refreshToken();

        if (! $refreshToken) {
            throw new \RuntimeException('Cannot refresh token: EBAY_REFRESH_TOKEN not configured');
        }

        $this->accessToken = $this->obtainAccessTokenFromRefreshToken($refreshToken);
    }

    /**
     * Make a token request to eBay OAuth endpoint
     */
    private function makeTokenRequest(array $data, string $credentials): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.ebay.com/identity/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic '.$credentials,
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL error: {$error}");
        }

        if ($httpCode !== 200) {
            throw new \RuntimeException("Token request failed with HTTP {$httpCode}: {$response}");
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON response from eBay token endpoint');
        }

        return $decodedResponse;
    }

    /**
     * Get OAuth authorization URL for user consent (if needed)
     */
    public function getAuthorizationUrl(?string $state = null): string
    {
        $clientId = EbayConfig::clientId();
        $redirectUri = EbayConfig::redirectUri();

        if (! $clientId || ! $redirectUri) {
            throw new \RuntimeException('eBay OAuth configuration incomplete');
        }

        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => 'https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/buy.item.feed',
            'state' => $state ?: bin2hex(random_bytes(16)),
        ];

        return 'https://auth.ebay.com/oauth2/authorize?'.http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $authorizationCode): array
    {
        $clientId = EbayConfig::clientId();
        $clientSecret = EbayConfig::clientSecret();
        $redirectUri = EbayConfig::redirectUri();

        if (! $clientId || ! $clientSecret || ! $redirectUri) {
            throw new \RuntimeException('eBay OAuth configuration incomplete');
        }

        $credentials = base64_encode($clientId.':'.$clientSecret);

        $response = $this->makeTokenRequest([
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
            'redirect_uri' => $redirectUri,
        ], $credentials);

        // Store the tokens
        $this->accessToken = $response['access_token'];
        $this->tokenExpiresAt = time() + ($response['expires_in'] ?? 7200);

        return $response;
    }
}
