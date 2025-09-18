<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts;

/**
 * Contract for repositories wrapping persistence or external APIs.
 * Exposes typical CRUD and query operations in terms of Domain DTOs.
 */
interface RepositoryContract
{
    /**
     * Persist or update a domain object.
     *
     * @param object $domainDto
     * @return object Updated domain DTO (with IDs, resource names, etc).
     */
    public function save(object $domainDto): object;

    /**
     * Find a domain object by ID or resource name.
     *
     * @param string|int $id
     * @return object|null
     */
    public function find(string|int $id): ?object;

    /**
     * Delete a domain object by ID or resource name.
     *
     * @param string|int $id
     * @return bool
     */
    public function delete(string|int $id): bool;

    /**
     * Fetch all domain objects by optional criteria.
     *
     * @param array<string,mixed> $criteria
     * @return iterable<object>
     */
    public function all(array $criteria = []): iterable;
}
