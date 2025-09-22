<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth\TokenStore;

use Plenipotentiary\Laravel\Contracts\Token\TokenStoreContract;

final class InMemoryTokenStore implements TokenStoreContract
{
    /** @var array<string, array{value:string,expires:int}> */
    private array $cache = [];

    public function get(string $key): ?string
    {
        $now = time();
        if (isset($this->cache[$key]) && $this->cache[$key]['expires'] > $now) {
            return $this->cache[$key]['value'];
        }
        unset($this->cache[$key]);
        return null;
    }

    public function put(string $key, string $value, int $ttlSeconds): void
    {
        $this->cache[$key] = ['value' => $value, 'expires' => time() + $ttlSeconds];
    }

    public function forget(string $key): void
    {
        unset($this->cache[$key]);
    }

    public function expiresAt(string $key): ?int
    {
        return $this->cache[$key]['expires'] ?? null;
    }
}
