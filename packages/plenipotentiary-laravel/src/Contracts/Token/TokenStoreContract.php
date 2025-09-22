<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Token;

interface TokenStoreContract
{
    public function get(string $key): ?string;
    public function put(string $key, string $value, int $ttlSeconds): void;
    public function forget(string $key): void;

    /**
     * Optional observability: when does this token expire?
     */
    public function expiresAt(string $key): ?int;
}
