<?php
declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Gaql;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\{Lookup, Criterion, Sort, Op, Dir};

/**
 * GAQL builder, resource-agnostic.
 * Supply the GAQL table name, the SELECT list, and a canonical→GAQL field map.
 */
final class QueryBuilder
{
    /**
     * @param non-empty-string $resource   GAQL FROM target, e.g. 'campaign'
     * @param list<non-empty-string> $selectColumns  GAQL columns to select
     * @param array<string, non-empty-string> $fieldMap canonicalField => gaqlColumn
     * @return array{query:string,pageToken:?string}
     */
    public function build(string $resource, array $selectColumns, Lookup $lookup, array $fieldMap): array
    {
        if ($resource === '') {
            throw new \InvalidArgumentException('resource must be non-empty');
        }
        if ($selectColumns === []) {
            throw new \InvalidArgumentException('selectColumns must not be empty');
        }

        $select = implode(', ', $selectColumns);
        $sql = "SELECT {$select} FROM {$resource}";

        $where = $this->buildWhere($lookup->whereClauses(), $fieldMap);
        if ($where !== '') {
            $sql .= " WHERE {$where}";
        }

        $order = $this->buildOrder($lookup->orderClauses(), $fieldMap);
        if ($order !== '') {
            $sql .= " ORDER BY {$order}";
        }

        if (($limit = $lookup->limit()) !== null) {
            $sql .= " LIMIT {$limit}";
        }

        return ['query' => $sql, 'pageToken' => $lookup->cursor()];
    }

    /**
     * @param list<Criterion> $criteria
     * @param array<string,string> $fieldMap
     */
    private function buildWhere(array $criteria, array $fieldMap): string
    {
        if ($criteria === []) return '';
        $parts = [];
        foreach ($criteria as $c) {
            $col = $this->col($c->field, $fieldMap);
            $parts[] = match ($c->op) {
                Op::Eq         => "{$col} = " . $this->lit($c->value),
                Op::In         => "{$col} IN (" . $this->list($c->value) . ")",
                Op::NotIn      => "{$col} NOT IN (" . $this->list($c->value) . ")",
                Op::Like       => "{$col} LIKE " . $this->lit('%' . $this->str($c->value) . '%'),
                Op::StartsWith => "{$col} LIKE " . $this->lit($this->str($c->value) . '%'),
                Op::Between    => $this->between($col, $c->value),
            };
        }
        return implode(' AND ', $parts);
    }

    /**
     * @param list<Sort> $orders
     * @param array<string,string> $fieldMap
     */
    private function buildOrder(array $orders, array $fieldMap): string
    {
        if ($orders === []) return '';
        $parts = [];
        foreach ($orders as $s) {
            $col = $this->col($s->field, $fieldMap);
            $parts[] = $col . ' ' . ($s->dir === Dir::Desc ? 'DESC' : 'ASC');
        }
        return implode(', ', $parts);
    }

    private function col(string $canonicalField, array $fieldMap): string
    {
        if (!isset($fieldMap[$canonicalField])) {
            throw new \InvalidArgumentException("Unknown field '{$canonicalField}'");
        }
        return $fieldMap[$canonicalField];
    }

    /** Quote a single literal */
    private function lit(mixed $v): string
    {
        if (is_int($v) || is_float($v)) {
            return (string) $v;
        }
        if (is_bool($v)) {
            return $v ? 'TRUE' : 'FALSE';
        }
        if ($v === null) {
            return 'NULL';
        }
        return "'" . $this->esc((string) $v) . "'";
    }

    /** @param mixed $v @return string */
    private function str(mixed $v): string
    {
        return (string) $v;
    }

    /** @param mixed $values */
    private function list(mixed $values): string
    {
        if (!is_iterable($values)) {
            throw new \InvalidArgumentException('IN/NOT IN expects an array of values');
        }
        $lits = [];
        foreach ($values as $v) {
            $lits[] = $this->lit($v);
        }
        if ($lits === []) {
            throw new \InvalidArgumentException('IN/NOT IN list must not be empty');
        }
        return implode(', ', $lits);
    }

    /** @param mixed $range */
    private function between(string $col, mixed $range): string
    {
        if (!is_array($range) || !array_key_exists(0, $range) || !array_key_exists(1, $range)) {
            throw new \InvalidArgumentException('Between expects [min, max]');
        }
        return "{$col} BETWEEN " . $this->lit($range[0]) . " AND " . $this->lit($range[1]);
    }

    private function esc(string $s): string
    {
        return str_replace("'", "\\'", $s);
    }
}
