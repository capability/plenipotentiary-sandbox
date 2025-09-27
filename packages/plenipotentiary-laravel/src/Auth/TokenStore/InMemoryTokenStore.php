<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth\TokenStore;

use Plenipotentiary\Laravel\Contracts\Token\TokenStoreContract;

/**
 * In-memory implementation of the TokenStoreContract.
 *
 * Stores tokens inside the PHP process lifecycle only,
 * meaning values are lost between requests or worker restarts.
 *
 * Useful for local development and testing, but NOT recommended for
 * production where multiple processes or machines are used.
 */
final class InMemoryTokenStore implements TokenStoreContract
{
    /** @var array<string, array{value:string,expires:int}> */
    private array $cache = [];

    /**
     * Retrieve a token if it exists and has not expired.
     */
    public function get(string $key): ?string
    {
        $now = time();
        if (isset($this->cache[$key]) && $this->cache[$key]['expires'] > $now) {
            return $this->cache[$key]['value'];
        }
        unset($this->cache[$key]);
        return null;
    }

    /**
     * Store a token with a time-to-live (TTL).
     */
    public function put(string $key, string $value, int $ttlSeconds): void
    {
        $this->cache[$key] = ['value' => $value, 'expires' => time() + $ttlSeconds];
    }

    /**
     * Remove a token from the store.
     */
    public function forget(string $key): void
    {
        unset($this->cache[$key]);
    }

    /**
     * Get the expiration timestamp for a given token key, if set.
     *
     * @return int|null UNIX timestamp or null if token not found
     */
    public function expiresAt(string $key): ?int
    {
        return $this->cache[$key]['expires'] ?? null;
    }
}
