<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsRequest;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Criterion;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Dir;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Op;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Page;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class ReadManyOperation
{
    private const FIELD_MAP = [
        'id' => 'campaign.id',
        'resourceName' => 'campaign.resource_name',
        'name' => 'campaign.name',
        'status' => 'campaign.status',
        'budgetResourceName' => 'campaign.campaign_budget',
    ];

    private const SELECT = [
        'campaign.resource_name',
        'campaign.id',
        'campaign.name',
        'campaign.status',
        'campaign.campaign_budget',
    ];

    public function __construct(
        private ProviderClientContract $client,
        private LoggerInterface $logger,
    ) {}

    public function perform(Lookup $criteria, string $customerId): Result
    {
        try {
            $this->spec($customerId);
        } catch (ValidationException $e) {
            return Result::invalid($e->toArray());
        }

        $request = $this->requestMapper($criteria, $customerId);

        $this->logger->info('Executing Google Ads campaign readMany', [
            'customerId' => $customerId,
            'query' => $request->getQuery(),
        ]);

        $response = $this->client->raw()
            ->getGoogleAdsServiceClient()
            ->search($request);

        return Result::ok($this->responseMapper($response));
    }

    public function spec(string $customerId): void
    {
        if ($customerId === '') {
            throw ValidationException::fromArray('campaign.readMany', [[
                'field' => 'customerId',
                'rule' => 'required',
                'mapsTo' => 'customerId',
            ]]);
        }
    }

    private function requestMapper(Lookup $criteria, string $customerId): SearchGoogleAdsRequest
    {
        $query = $this->buildQuery($criteria);

        $request = (new SearchGoogleAdsRequest)
            ->setCustomerId($customerId)
            ->setQuery($query);

        if ($criteria->cursor()) {
            $request->setPageToken($criteria->cursor());
        }

        if ($criteria->limit()) {
            $request->setPageSize($criteria->limit());
        }

        return $request;
    }

    private function responseMapper(object $response): Page
    {
        $items = [];
        foreach ($response->iterateAllElements() as $row) {
            $campaign = $row->getCampaign();

            $items[] = CampaignCanonicalDTO::fromArray([
                'externalId' => (string) $campaign->getId(),
                'name' => $campaign->getName(),
                'status' => $campaign->getStatus(),
                'budgetResourceName' => $campaign->getCampaignBudget(),
                'identifiers' => [
                    'resourceName' => $campaign->getResourceName(),
                ],
                'providerContext' => [
                    'resourceName' => $campaign->getResourceName(),
                ],
            ]);
        }

        $nextToken = method_exists($response, 'getNextPageToken') ? $response->getNextPageToken() : null;

        return new Page($items, $nextToken ?: null);
    }

    private function buildQuery(Lookup $criteria): string
    {
        $select = implode(', ', self::SELECT);
        $sql = "SELECT {$select} FROM campaign";

        $where = array_map(fn (Criterion $c) => $this->mapCriterion($c), $criteria->whereClauses());
        if ($where) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }

        $order = array_map(fn ($sort) => $this->mapSort($sort->field, $sort->dir), $criteria->orderClauses());
        if ($order) {
            $sql .= ' ORDER BY '.implode(', ', $order);
        }

        if ($criteria->limit()) {
            $sql .= ' LIMIT '.(int) $criteria->limit();
        }

        return $sql;
    }

    private function mapField(string $field): string
    {
        if (! isset(self::FIELD_MAP[$field])) {
            throw new \InvalidArgumentException("Unknown lookup field {$field}");
        }

        return self::FIELD_MAP[$field];
    }

    private function escape(string $value): string
    {
        return str_replace("'", "\\'", $value);
    }

    private function mapCriterion(Criterion $criterion): string
    {
        $column = $this->mapField($criterion->field);

        return match ($criterion->op) {
            Op::Eq => sprintf("%s = '%s'", $column, $this->escape((string) $criterion->value)),
            Op::In => sprintf('%s IN (%s)', $column, $this->csv($criterion->value)),
            Op::NotIn => sprintf('%s NOT IN (%s)', $column, $this->csv($criterion->value)),
            Op::Like => sprintf("%s LIKE '%%%s%%'", $column, $this->escape((string) $criterion->value)),
            Op::StartsWith => sprintf("%s LIKE '%s%%'", $column, $this->escape((string) $criterion->value)),
            Op::Between => sprintf("%s BETWEEN '%s' AND '%s'",
                $column,
                $this->escape((string) $criterion->value[0]),
                $this->escape((string) $criterion->value[1])
            ),
        };
    }

    private function csv(mixed $values): string
    {
        return implode(', ', array_map(
            fn ($v) => "'".$this->escape((string) $v)."'",
            (array) $values
        ));
    }

    private function mapSort(string $field, Dir $direction): string
    {
        return sprintf('%s %s', $this->mapField($field), $direction === Dir::Desc ? 'DESC' : 'ASC');
    }
}
