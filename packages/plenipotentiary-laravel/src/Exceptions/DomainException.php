<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Exceptions;

use JsonSerializable;
use RuntimeException;
use Throwable;

/**
 * Base exception returned by provider error mappers. Carries structured
 * metadata so gateways can surface consistent Result payloads.
 */
class DomainException extends RuntimeException implements JsonSerializable
{
    public function __construct(
        private readonly string $errorCode,
        string $message,
        public readonly int $httpStatus = 400,
        public readonly bool $retryable = false,
        public readonly array $meta = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function code(): string
    {
        return $this->errorCode;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }

    /**
     * @return array<string,mixed>
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * Provide a predictable JSON structure for logging or API responses.
     */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->errorCode,
            'message' => $this->getMessage(),
            'httpStatus' => $this->httpStatus,
            'retryable' => $this->retryable,
            'meta' => $this->meta,
        ];
    }
}
