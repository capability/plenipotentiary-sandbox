<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\Generated;

use Plenipotentiary\Laravel\Contracts\RepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdDomainData;

/**
 * Generated AdRepository with stub methods.
 * This file may be overwritten by scaffolding.
 */
class AdRepository implements RepositoryContract
{
    public function all(array $criteria = []): iterable
    {
        // TODO: Implement all() for Ad
        return [];
    }

    public function find(string|int $id): ?object
    {
        // TODO: Implement find() for Ad
        return null;
    }

    public function save(object $domainDto): object
    {
        \assert($domainDto instanceof AdDomainData);

        // TODO: Implement save() for Ad
        return $domainDto;
    }

    public function delete(string|int $id): bool
    {
        // TODO: Implement delete() for Ad
        return false;
    }
}
