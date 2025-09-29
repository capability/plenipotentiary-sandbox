<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Token\TokenStoreContract;
use Psr\Http\Message\RequestInterface;

final class OAuth2ClientCredentialsStrategy implements AuthStrategyContract
{
    /** @var callable(array):array|null */
    private $httpClient;

    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $tokenUrl,
        private ?string $scope,
        private ?string $audience,
        private TokenStoreContract $store,
        private int $cacheTtl = 3300,
        ?callable $httpClient = null
    ) {
        $this->httpClient = $httpClient;
    }

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $cacheKey = $this->cacheKey();
        $token = $this->store->get($cacheKey);

        if (! $token) {
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

    private function fetchToken(): string
    {
        $payload = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ] + array_filter([
            'scope' => $this->scope,
            'audience' => $this->audience,
        ], fn($v) => $v !== null);

        $client = $this->httpClient ?? function (array $form) {
            $opts = ['http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($form),
                'timeout' => 10,
            ]];
            $resp = file_get_contents($this->tokenUrl, false, stream_context_create($opts));
            if ($resp === false) {
                throw new \RuntimeException('OAuth2 token request failed');
            }
            $data = json_decode($resp, true) ?? [];
            if (! isset($data['access_token'])) {
                throw new \RuntimeException('OAuth2 token response missing access_token');
            }
            return $data;
        };

        $res = $client($payload);

        return (string) $res['access_token'];
    }
}