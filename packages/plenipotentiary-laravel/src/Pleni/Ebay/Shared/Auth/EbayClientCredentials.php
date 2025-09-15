<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Ebay\Shared\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Plenipotentiary\Laravel\Contracts\AuthStrategy;

/**
 * eBay OAuth2 Client Credentials flow (Application token).
 * This implementation caches tokens and applies them to outgoing requests.
 */
class EbayClientCredentials implements AuthStrategy
{
    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $tokenUrl,
        private readonly array $scopes,
    ) {}

    public function apply(array $options = []): array
    {
        $token = $this->getAccessToken();

        $options['headers']['Authorization'] = 'Bearer '.$token;

        return $options;
    }

    private function getAccessToken(): string
    {
        return Cache::remember($this->cacheKey(), 3300, function (): string {
            $response = Http::asForm()->withBasicAuth($this->clientId, $this->clientSecret)
                ->post($this->tokenUrl, [
                    'grant_type' => 'client_credentials',
                    'scope' => implode(' ', $this->scopes),
                ]);

            if (! $response->ok()) {
                throw new \RuntimeException('Failed to fetch eBay access token: '.$response->body());
            }

            return $response->json()['access_token'] ?? '';
        });
    }

    private function cacheKey(): string
    {
        return 'ebay_oauth_'.md5(implode(' ', $this->scopes));
    }
}
