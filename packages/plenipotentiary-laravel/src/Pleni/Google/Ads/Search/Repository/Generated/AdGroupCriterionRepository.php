<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\Generated;

use Plenipotentiary\Laravel\Contracts\RepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupCriterionDomainData;

/**
 * Generated AdGroupCriterionRepository with stub methods.
 * This file may be overwritten by scaffolding.
 *
 * @implements RepositoryContract<AdGroupCriterionDomainData>
 */
class AdGroupCriterionRepository implements RepositoryContract
{
    public function all(array $criteria = []): iterable
    {
        // TODO: Implement all() for AdGroupCriterion
        return [];
    }

    public function find(string|int $id): ?object
    {
        // TODO: Implement find() for AdGroupCriterion
        return null;
    }

    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof AdGroupCriterionDomainData);

        // TODO: Implement save() for AdGroupCriterion
        return $domainDto;
    }

    public function delete(string|int $id): bool
    {
        // TODO: Implement delete() for AdGroupCriterion
        return false;
    }
}
