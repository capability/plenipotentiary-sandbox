<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\Generated;

use Plenipotentiary\Laravel\Contracts\RepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupDomainData;

/**
 * Generated AdGroupRepository with stub methods.
 * This file may be overwritten by scaffolding.
 */
class AdGroupRepository implements RepositoryContract
{
    public function all(array $criteria = []): iterable
    {
        // TODO: Implement all() for AdGroup
        return [];
    }

    public function find(string|int $id): ?object
    {
        // TODO: Implement find() for AdGroup
        return null;
    }

    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof AdGroupDomainData);

        // TODO: Implement save() for AdGroup
        return $domainDto;
    }

    public function delete(string|int $id): bool
    {
        // TODO: Implement delete() for AdGroup
        return false;
    }
}
