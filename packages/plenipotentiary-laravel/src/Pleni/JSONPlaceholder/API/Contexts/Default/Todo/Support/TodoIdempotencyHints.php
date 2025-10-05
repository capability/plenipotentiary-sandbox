<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Support;

use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;

/**
 * Provides idempotency hints for Todo operations.
 */
final class TodoIdempotencyHints implements IdempotencyHints
{
    public function isIdempotent(string $operation, array $payload): bool
    {
        // JSONPlaceholder is a fake API, so technically all operations are idempotent
        // In a real implementation, you'd determine this based on the operation
        return match ($operation) {
            'todo.create' => false, // Creates generate new IDs
            'todo.update' => true,  // Updates are idempotent if same data
            'todo.delete' => true,  // Deleting same ID is idempotent
            'todo.find', 'todo.list' => true, // Reads are always idempotent
            default => false,
        };
    }

    public function extractIdempotencyKey(string $operation, array $payload): ?string
    {
        // For update/delete, use the ID as the idempotency key
        return match ($operation) {
            'todo.update', 'todo.delete' => isset($payload['id']) ? "todo.{$operation}.{$payload['id']}" : null,
            'todo.create' => null, // Creates don't have natural idempotency keys
            default => null,
        };
    }

    public function ttl(string $operation): ?int
    {
        // Cache idempotency checks for 1 hour
        return 3600;
    }
}
