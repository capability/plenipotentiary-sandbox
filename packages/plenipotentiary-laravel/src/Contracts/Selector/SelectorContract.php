<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Selector;

/**
 * Provider-agnostic selector contract for identifying remote resources.
 */
interface SelectorContract
{
    /** Unique selector type within the domain (e.g. external_id, resource_name). */
    public function type(): string;

    /** Raw value associated with the selector (id, resource name, etc.). */
    public function value(): string;

    /**
     * Provider-specific context hints (customer ids, resource names, etc.).
     *
     * @return array<string,string>
     */
    public function providerContext(): array;
}
