<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Support;

use Throwable;

/**
 * Represents a mapped error from a provider-specific exception.
 * 
 * This class provides a consistent way to represent errors from
 * different providers, including metadata about retryability,
 * HTTP status codes, and error codes.
 */
final class MappedError extends \Exception
{
    public function __construct(
        private readonly string $code,
        string $message,
        private readonly int $httpStatus,
        private readonly bool $retryable,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }

    public function code(): string
    {
        return $this->code;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }
}
