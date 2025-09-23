<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\DTO\ContextualInboundDTOContract;

/**
 * Contract for any API CRUD Adapter that integrates with an external API.
 *
 * Adapters are responsible for the actual communication layer and translation
 * of DTOs into API-specific formats and back.
 */
interface ApiCrudAdapterContract
{
    public function create(OutboundDTOContract $dto): ContextualInboundDTOContract;

    public function read(OutboundDTOContract $dto): ?ContextualInboundDTOContract;

    public function update(OutboundDTOContract $dto): ContextualInboundDTOContract;

    public function delete(OutboundDTOContract $dto): ContextualInboundDTOContract;

    /**
     * @return iterable<ContextualInboundDTOContract>
     */
    public function listAll(array $criteria = []): iterable;
}
