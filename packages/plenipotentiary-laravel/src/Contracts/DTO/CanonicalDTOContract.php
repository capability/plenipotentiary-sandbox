<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\DTO;

/**
 * Canonical Data Transfer Object Contract.
 *
 * All canonical DTOs must expose schema, serialization, and context methods.
 */
interface CanonicalDTOContract
{
    /**
     * Defines the canonical shape expected by factories/controllers when hydrating this DTO.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function schema(): array;

    /**
     * Build a DTO from array payload.
     *
     * @param  array<string,mixed>  $data
     */
    public static function fromArray(array $data): self;

    /**
     * Convert DTO back into array form.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array;

    /**
     * Provider context manipulation.
     *
     * @param array<string,string> $context
     */
    public function setProviderContext(array $context): void;

    /** @param array<string,string> $context */
    public function mergeProviderContext(array $context): void;

    public function getProviderContextValue(string $key): ?string;
}
