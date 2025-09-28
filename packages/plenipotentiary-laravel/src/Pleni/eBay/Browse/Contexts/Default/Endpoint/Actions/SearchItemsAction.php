<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Endpoint\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiEndpointGatewayContract;
use Plenipotentiary\Laravel\Pleni\Support\Result;

/**
 * Search Items Action
 * 
 * Business operation for searching eBay items.
 */
class SearchItemsAction
{
    use AsAction;

    public function __construct(
        private ApiEndpointGatewayContract $gateway,
    ) {}

    public function handle(string $query, array $filters = []): Result
    {
        $payload = [
            'q' => $query,
            'limit' => $filters['limit'] ?? 50,
        ];

        // Add optional filters
        if (!empty($filters['categories'])) {
            $payload['category_ids'] = $filters['categories'];
        }

        if (!empty($filters['price_range'])) {
            if (isset($filters['price_range']['min'])) {
                $payload['filter'] = $payload['filter'] ?? [];
                $payload['filter'][] = "price:[{$filters['price_range']['min']}..]";
            }
            if (isset($filters['price_range']['max'])) {
                $payload['filter'] = $payload['filter'] ?? [];
                $payload['filter'][] = "price:[..{$filters['price_range']['max']}]";
            }
        }

        if (!empty($filters['condition'])) {
            $payload['filter'] = $payload['filter'] ?? [];
            $payload['filter'][] = "conditionIds:{$filters['condition']}";
        }

        if (!empty($filters['sort'])) {
            $payload['sort'] = $filters['sort'];
        }

        return $this->gateway->call('searchItems', $payload);
    }
}
