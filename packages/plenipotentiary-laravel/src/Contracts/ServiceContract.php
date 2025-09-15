<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts;

/**
 * General CRUD service contract that all User services may implement.
 * Includes CRUD-style operations as an optional seam,
 * so individual services can choose to support them.
 */
interface CrudServiceContract
{
    /**
     * Create a new resource.
     */
    public function create(object $domainDto): object;

    /**
     * Read a resource by ID.
     */
    public function read(string|int $id): ?object;

    /**
     * Update a resource.
     */
    public function update(object $domainDto): object;

    /**
     * Delete a resource by ID.
     */
    public function delete(string|int $id): bool;

    // NOTE: Existing service methods (like search, sync, etc.) continue to live
    // in the concrete service interfaces/classes. These CRUD signatures provide
    // a stable seam across providers/resources.
}
