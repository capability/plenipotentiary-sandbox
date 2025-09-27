<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Support\Operation;

/**
 * Thrown when local preflight validation fails.
 */
final class ValidationException extends \RuntimeException implements \JsonSerializable
{
    /**
     * @param string $operation
     * @param array<int,array<string,mixed>> $violations
     */
    private function __construct(
        private readonly string $operation,
        private readonly array $violations
    ) {
        parent::__construct("Validation failed for {$operation}");
    }

    /**
     * @param string $operation
     * @param array<int,array<string,mixed>> $violations
     */
    public static function fromArray(string $operation, array $violations): self
    {
        return new self($operation, $violations);
    }

    /** @return array<int,array<string,mixed>> */
    public function violations(): array
    {
        return $this->violations;
    }

    public function toArray(): array
    {
        return [
            'operation'  => $this->operation,
            'violations' => $this->violations,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
