<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Token\TokenStoreContract;
use Psr\Http\Message\RequestInterface;

/**
 * OAuth2 Client Credentials authentication strategy.
 *
 * This strategy fetches an OAuth2 access token using the
 * "client_credentials" grant type and applies it as a Bearer token.
 *
 * Tokens are cached using the injected TokenStoreContract to avoid
 * excess token requests. The default cache TTL is ~55 minutes.
 *
 * Supports optional scope and audience values, and allows a custom
 * HTTP client callable to be provided for testing or alternative transport.
 */
final class OAuth2ClientCredentialsStrategy implements AuthStrategyContract
{
    /**
     * @param string                 $clientId    OAuth2 client id
     * @param string                 $clientSecret OAuth2 client secret
     * @param string                 $tokenUrl    Token endpoint URL
     * @param string|null             $scope       Optional OAuth2 scopes
     * @param string|null             $audience    Optional audience value
     * @param TokenStoreContract      $store       Token store implementation (cache)
     * @param int                     $cacheTtl    TTL for tokens (seconds)
     * @param callable(array):array|null $httpClient Optional HTTP client override, must return decoded JSON array
     */
    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $tokenUrl,
        private ?string $scope = null,
        private ?string $audience = null,
        private TokenStoreContract $store,
        private int $cacheTtl = 3300,
        private ?callable $httpClient = null
    ) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $cacheKey = $this->cacheKey();
        $token = $this->store->get($cacheKey);

        if (!$token) {
            $token = $this->fetchToken();
            $this->store->put($cacheKey, $token, $this->cacheTtl);
        }

        return $request->withHeader('Authorization', 'Bearer '.$token);
    }

    private function cacheKey(): string
    {
        return 'pleni:o2cc:'.md5(json_encode([
            'cid' => $this->clientId,
            'url' => $this->tokenUrl,
            'scope' => $this->scope,
            'aud' => $this->audience,
        ]));
    }

    /**
     * Fetch a fresh access token from the OAuth2 server.
     *
     * @throws \RuntimeException if the request fails or token is missing
     */
    private function fetchToken(): string
    {
        $payload = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
        if ($this->scope)    $payload['scope'] = $this->scope;
        if ($this->audience) $payload['audience'] = $this->audience;

        $client = $this->httpClient ?? function(array $form) {
            $opts = ['http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($form),
                'timeout' => 10,
            ]];
            $resp = file_get_contents($this->tokenUrl, false, stream_context_create($opts));
            if ($resp === false) {
                throw new \RuntimeException('OAuth2 token request failed');
            }
            $data = json_decode($resp, true) ?? [];
            if (!isset($data['access_token'])) {
                throw new \RuntimeException('OAuth2 token response missing access_token');
            }
            return $data;
        };

        $res = $client($payload);
        return (string) $res['access_token'];
    }
}
