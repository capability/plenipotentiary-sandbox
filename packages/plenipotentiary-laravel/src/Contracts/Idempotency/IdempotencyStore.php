<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Idempotency;

/**
 * Store interface for short-term idempotency (e.g. retries/races).
 */
interface IdempotencyStore
{
    public function get(string $scope, string $fingerprint): ?string;
    public function put(string $scope, string $fingerprint, string $value): void;

    public function tombstone(string $scope, string $fingerprint): void;
    public function isTombstoned(string $scope, string $fingerprint): bool;
}
