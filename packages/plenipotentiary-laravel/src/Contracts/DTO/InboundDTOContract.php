<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\DTO;

/**
 * Minimal contract for inbound (external → domain) DTOs.
 *
 * Gateways and services should typehint this to keep provider noise out.
 * Exposes only the core identity/status surface, plus rawResponse for debugging.
 */
interface InboundDTOContract
{
    /**
     * Canonical external identifier for the provider resource.
     * May be null for browse/search APIs without stable IDs.
     */
    public function getExternalResourceId(): ?string;

    /** Human‑friendly label/title if available */
    public function getExternalResourceLabel(): ?string;

    /** Normalized cross‑provider status (active, paused, deleted, …) */
    public function getExternalResourceStatus(): string;

    /**
     * Original provider response snapshot (JSON‑safe array or JSON string).
     * Useful for logging/debugging; MUST be serializable.
     */
    public function getRawResponse(): array|string|null;

    /**
     * Serialize the DTO into an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array;
}
