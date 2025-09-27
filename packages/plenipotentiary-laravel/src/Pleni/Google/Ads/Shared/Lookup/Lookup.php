<?php
declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup;

/**
 * Immutable lookup container, fluent with-clone API.
 */
final class Lookup
{
    /** @var list<Criterion> */
    private array $where = [];
    /** @var list<Sort> */
    private array $order = [];
    private ?int $limit = 50;
    private ?string $cursor = null; // provider page token

    public static function make(): self
    {
        return new self();
    }

    /** @param list<Criterion> $criteria */
    public function withWhere(array $criteria): self
    {
        $clone = clone $this;
        $clone->where = array_values($criteria);
        return $clone;
    }

    public function where(Criterion $c): self
    {
        $clone = clone $this;
        $clone->where[] = $c;
        return $clone;
    }

    public function orderBy(string $field, Dir $dir = Dir::Asc): self
    {
        $clone = clone $this;
        $clone->order[] = new Sort($field, $dir);
        return $clone;
    }

    public function withLimit(?int $limit): self
    {
        if ($limit !== null && $limit < 1) {
            throw new \InvalidArgumentException('Limit must be >= 1 or null');
        }
        $clone = clone $this;
        $clone->limit = $limit;
        return $clone;
    }

    public function withCursor(?string $cursor): self
    {
        $clone = clone $this;
        $clone->cursor = $cursor;
        return $clone;
    }

    /** @return list<Criterion> */
    public function whereClauses(): array { return $this->where; }
    /** @return list<Sort> */
    public function orderClauses(): array { return $this->order; }
    public function limit(): ?int { return $this->limit; }
    public function cursor(): ?string { return $this->cursor; }
}
