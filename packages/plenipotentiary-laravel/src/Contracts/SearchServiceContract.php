<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts;

/**
 * Contract for services that provide search/list-style operations
 * (e.g. eBay Browse). Different from CRUD services.
 */
interface SearchServiceContract
{
    /**
     * Perform a search or list operation with a free-form query or criteria.
     *
     * @return iterable<object>
     */
    public function search(mixed $criteria): iterable;
}
