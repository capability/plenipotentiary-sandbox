<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup;

/**
 * @template T
 */
final class Page
{
    /** @param list<T> $items */
    public function __construct(
        public readonly array $items,
        public readonly ?string $nextCursor = null
    ) {}

    public function hasNext(): bool
    {
        return $this->nextCursor !== null && $this->nextCursor !== '';
    }

    /**
     * @template U
     * @param callable(T):U $fn
     * @return Page<U>
     */
    public function mapItems(callable $fn): self
    {
        $mapped = array_map($fn, $this->items);
        return new self($mapped, $this->nextCursor);
    }

    public function count(): int
    {
        return \count($this->items);
    }
}
