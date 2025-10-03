<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\Selector;

use Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\DTO\CustomerCanonicalDTO;

/**
 * Selector for identifying a specific Stripe customer.
 */
final class CustomerSelector
{
    private function __construct(
        private readonly string $externalId,
    ) {}

    public static function byExternalId(string $externalId): self
    {
        return new self($externalId);
    }

    public function value(): string
    {
        return $this->externalId;
    }

    public function toCanonicalDTO(): CustomerCanonicalDTO
    {
        return CustomerCanonicalDTO::fromArray([
            'externalId' => $this->externalId,
        ]);
    }
}
