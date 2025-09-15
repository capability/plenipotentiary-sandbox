<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts;

/**
 * Generic repository contract for plenipotentiary integrations.
 * Provides a uniform CRUD surface that Generated repositories implement.
 *
 * @template T of object
 */
interface RepositoryContract
{
    /**
     * Return a collection of domain DTOs matching criteria.
     *
     * @param array<string,mixed> $criteria
     * @return iterable<T>
     */
    public function all(array $criteria = []): iterable;

    /**
     * Find a domain DTO by its ID.
     *
     * @param string|int $id
     * @return T|null
     */
    public function find(string|int $id): ?object;

    /**
     * Save a domain DTO and return the updated/persisted instance.
     *
     * @param T $domainDto
     * @return T
     */
    public function save(object $domainDto): object;

    /**
     * Delete by ID.
     *
     * @param string|int $id
     */
    public function delete(string|int $id): bool;
}
