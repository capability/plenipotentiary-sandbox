<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Tests\Unit\Stubs;

use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;

final class FakeCanonicalDto implements CanonicalDTOContract
{
    /** @param array<string,string> $providerContext */
    public function __construct(
        public array $providerContext = [],
        public ?string $name = null,
        public ?int $amount = null,
        public ?int $micros = null,
        public mixed $custom = null,
    ) {}

    /**
     * @return array<string,array<string,mixed>>
     */
    public static function schema(): array
    {
        return [
            'name' => ['key' => 'name', 'cast' => 'string'],
            'amount' => ['key' => 'amount', 'cast' => 'int', 'default' => 42],
            'micros' => ['key' => 'micros', 'cast' => 'currency_to_micros'],
            'custom' => ['key' => 'custom', 'cast' => fn ($value) => is_string($value) ? strtoupper($value) : $value],
        ];
    }

    public static function fromArray(array $data): self
    {
        $dto = new self(
            providerContext: $data['providerContext'] ?? [],
            name: $data['name'] ?? null,
            amount: isset($data['amount']) ? (int) $data['amount'] : null,
            micros: isset($data['micros']) ? (int) $data['micros'] : null,
            custom: $data['custom'] ?? null,
        );

        return $dto;
    }

    public function toArray(): array
    {
        return [
            'providerContext' => $this->providerContext,
            'name' => $this->name,
            'amount' => $this->amount,
            'micros' => $this->micros,
            'custom' => $this->custom,
        ];
    }

    public function setProviderContext(array $context): void
    {
        $this->providerContext = $context;
    }

    public function mergeProviderContext(array $context): void
    {
        $this->providerContext = array_merge($this->providerContext, $context);
    }

    public function getProviderContextValue(string $key): ?string
    {
        return $this->providerContext[$key] ?? null;
    }
}
