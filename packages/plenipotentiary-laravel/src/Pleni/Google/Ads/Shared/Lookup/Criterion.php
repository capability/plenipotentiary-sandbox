<?php
declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup;

final class Criterion
{
    public function __construct(
        public readonly string $field,   // canonical field name
        public readonly Op $op,
        public readonly mixed $value     // scalar|array|[min,max] for Between
    ) {}

    public static function eq(string $field, mixed $value): self
    {
        return new self($field, Op::Eq, $value);
    }

    /** @param array<int,mixed> $values */
    public static function in(string $field, array $values): self
    {
        return new self($field, Op::In, $values);
    }

    /** @param array<int,mixed> $values */
    public static function notIn(string $field, array $values): self
    {
        return new self($field, Op::NotIn, $values);
    }

    public static function like(string $field, string $needle): self
    {
        return new self($field, Op::Like, $needle);
    }

    public static function startsWith(string $field, string $prefix): self
    {
        return new self($field, Op::StartsWith, $prefix);
    }

    /** @param array{0:mixed,1:mixed} $range */
    public static function between(string $field, array $range): self
    {
        if (!array_key_exists(0, $range) || !array_key_exists(1, $range)) {
            throw new \InvalidArgumentException('Between expects [min, max]');
        }
        return new self($field, Op::Between, [$range[0], $range[1]]);
    }
}
