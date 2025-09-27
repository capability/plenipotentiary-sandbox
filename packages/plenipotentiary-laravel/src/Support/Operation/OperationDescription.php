<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Support\Operation;

/**
 * Structured description of validation rules for a given operation.
 */
final class OperationDescription implements \JsonSerializable
{
    /**
     * @param string $operation identifier (e.g. 'campaign.create')
     * @param array<int,array<string,mixed>> $rules validation rules metadata
     */
    private function __construct(
        public readonly string $operation,
        public readonly array $rules = []
    ) {}

    /**
     * Convenience factory.
     *
     * @param string $op
     * @param array<int,array<string,mixed>> $rules
     */
    public static function make(string $op, array $rules): self
    {
        return new self($op, $rules);
    }

    public function toArray(): array
    {
        return [
            'operation' => $this->operation,
            'rules'     => $this->rules,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
