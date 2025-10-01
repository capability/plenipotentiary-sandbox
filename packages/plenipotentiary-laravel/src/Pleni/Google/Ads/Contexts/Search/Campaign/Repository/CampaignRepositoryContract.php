<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository;

use App\Models\AcmeCart\Search\Campaign;
use Illuminate\Support\Collection;

interface CampaignRepositoryContract
{
    /**
     * Example of domain-specific queries/aggregates that don't belong in a base repository.
     */
    public function findActive(): Collection;

    public function findByExternalReference(string $externalRef): ?Campaign;

    /**
     * Example extension for relationships: return campaign aggregate with related budgets.
     * @return array{campaign: Campaign, budgets: Collection}
     */
    public function findWithBudgets(string $id): array;
}
