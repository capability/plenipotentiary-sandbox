<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Plenipotentiary\Laravel\Contracts\AuthStrategy;

/**
 * AuthStrategy implementation for Google Ads using OAuth2 client credentials.
 * Fetches and caches access tokens, applies Bearer headers to API requests.
 */
class GoogleAdsClientCredentials implements AuthStrategy
{
    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected string $tokenUrl,
        protected array $scopes = []
    ) {}

    public function getHeaders(array $scopes = []): array
    {
        $token = $this->getAccessToken($scopes ?: $this->scopes);

        return [
            'Authorization' => 'Bearer '.$token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    protected function getAccessToken(array $scopes): string
    {
        $cacheKey = 'google_ads_token_'.md5(implode(' ', $scopes));

        return Cache::remember($cacheKey, 3500, function () use ($scopes) {
            $response = Http::asForm()->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => implode(' ', $scopes),
            ])->throw()->json();

            if (! isset($response['access_token'])) {
                throw new \RuntimeException('Failed to retrieve Google Ads access token');
            }

            return $response['access_token'];
        });
    }
}
