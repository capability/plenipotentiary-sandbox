<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Idempotency;

use Illuminate\Contracts\Cache\Repository as Cache;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyStore;

final class CacheIdempotencyStore implements IdempotencyStore
{
    public function __construct(
        private readonly Cache $cache,
        private readonly int $ttlSeconds = 3600 // default 1h
    ) {}

    public function get(string $scope, string $fp): ?string
    {
        return $this->cache->get($this->k($scope, $fp));
    }

    public function put(string $scope, string $fp, string $value): void
    {
        $this->cache->put($this->k($scope, $fp), $value, $this->ttlSeconds);
    }

    public function tombstone(string $scope, string $fp): void
    {
        $this->cache->put($this->k($scope, $fp), '__TOMBSTONE__', $this->ttlSeconds);
    }

    public function isTombstoned(string $scope, string $fp): bool
    {
        return $this->cache->get($this->k($scope, $fp)) === '__TOMBSTONE__';
    }

    private function k(string $scope, string $fp): string
    {
        return "pleni:idemp:{$scope}:{$fp}";
    }
}
