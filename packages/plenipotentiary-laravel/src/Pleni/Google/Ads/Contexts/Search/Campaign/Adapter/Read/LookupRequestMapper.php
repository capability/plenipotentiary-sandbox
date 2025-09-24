<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Read;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\{Lookup as CampaignLookup, Criterion, Op, Dir};

final class LookupRequestMapper
{
    /** canonical → GAQL */
    private const FIELD = [
        'id'                => 'campaign.id',
        'resourceName'      => 'campaign.resource_name',
        'name'              => 'campaign.name',
        'status'            => 'campaign.status',
        'budgetResourceName'=> 'campaign.campaign_budget',
        // add whitelisted fields only
    ];

    /** SELECT list for canonical mapping */
    private const SELECT = [
        'campaign.resource_name',
        'campaign.id',
        'campaign.name',
        'campaign.status',
        'campaign.campaign_budget',
    ];

    public function toQuery(string $customerId, CampaignLookup $q): array
    {
        $where = array_map(fn(Criterion $c) => $this->crit($c), $q->where);
        $order = array_map(fn($s) => $this->ord($s->field, $s->dir), $q->order);

        $select = implode(', ', self::SELECT);
        $sql = "SELECT {$select} FROM campaign";
        if ($where) $sql .= " WHERE ".implode(' AND ', $where);
        if ($order) $sql .= " ORDER BY ".implode(', ', $order);
        if ($q->limit) $sql .= " LIMIT {$q->limit}";

        return ['query' => $sql, 'pageToken' => $q->cursor];
    }

    private function col(string $field): string
    {
        if (!isset(self::FIELD[$field])) throw new \InvalidArgumentException("Unknown field {$field}");
        return self::FIELD[$field];
    }

    private function esc(string $v): string
    {
        return str_replace("'", "\\'", $v);
    }

    private function crit(Criterion $c): string
    {
        $col = $this->col($c->field);
        return match ($c->op) {
            Op::Eq        => "{$col} = '{$this->esc((string)$c->value)}'",
            Op::In        => "{$col} IN (".implode(', ', array_map(fn($v)=>"'".$this->esc((string)$v)."'", (array)$c->value)).")",
            Op::NotIn     => "{$col} NOT IN (".implode(', ', array_map(fn($v)=>"'".$this->esc((string)$v)."'", (array)$c->value)).")",
            Op::Like      => "{$col} LIKE '%".$this->esc((string)$c->value)."%'",
            Op::StartsWith=> "{$col} LIKE '".$this->esc((string)$c->value)."%'",
            Op::Between   => "{$col} BETWEEN '".$this->esc((string)$c->value[0])."' AND '".$this->esc((string)$c->value[1])."'",
        };
    }

    private function ord(string $field, Dir $dir): string
    {
        return $this->col($field).' '.($dir === Dir::Desc ? 'DESC' : 'ASC');
    }
}
