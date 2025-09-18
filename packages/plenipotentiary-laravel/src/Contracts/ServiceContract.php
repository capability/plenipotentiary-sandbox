<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts;

/**
 * Explicit CRUD contract for API-context services.
 *
 * This contract formalizes the Create / Read / Update / Delete lifecycle
 * for API-domain resources (e.g. CampaignService).
 *
 * Notes:
 * - DTOs must always be strongly-typed domain or external DTOs.
 * - Read/list/search operations can be extended in the concrete service
 *   (e.g. `listAll`, `searchByCriteria`) but `read()` remains the single-item seam.
 */
interface ApiCrudServiceContract
{
    /**
     * Create a new resource in the external provider or persistence layer.
     *
     * @param object $domainDto A Domain or External DTO defining the new resource.
     * @return object The created Domain or External DTO.
     */
    public function create(object $domainDto): object;

    /**
     * Read a resource by its identifier.
     *
     * @param string|int $id The provider- or database-level identifier.
     * @return ?object The found resource DTO or null if not found.
     */
    public function read(string|int $id): ?object;

    /**
     * Update an existing resource.
     *
     * @param object $domainDto A DTO with updated information.
     * @return object The updated resource DTO.
     */
    public function update(object $domainDto): object;

    /**
     * Delete a resource by its identifier.
     *
     * @param string|int $id The resource identifier.
     * @return bool Whether the resource was successfully deleted.
     */
    public function delete(string|int $id): bool;
}
