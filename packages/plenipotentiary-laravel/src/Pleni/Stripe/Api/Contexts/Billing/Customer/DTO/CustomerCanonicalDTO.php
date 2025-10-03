<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\DTO;

use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;

/**
 * Canonical DTO for Stripe Customer.
 *
 * Provider-agnostic representation of a customer.
 */
final class CustomerCanonicalDTO implements CanonicalDTOContract
{
    public function __construct(
        public ?string $externalId = null,
        public ?string $email = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?array $metadata = null,
        public array $providerContext = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            externalId: $data['externalId'] ?? null,
            email: $data['email'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? null,
            providerContext: $data['providerContext'] ?? [],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'externalId' => $this->externalId,
            'email' => $this->email,
            'name' => $this->name,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'providerContext' => $this->providerContext,
        ], fn ($value) => $value !== null);
    }

    public function getProviderContextValue(string $key): mixed
    {
        return data_get($this->providerContext, $key);
    }

    public function setProviderContext(array $context): void
    {
        $this->providerContext = $context;
    }
}
