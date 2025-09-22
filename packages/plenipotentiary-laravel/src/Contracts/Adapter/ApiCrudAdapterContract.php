<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;

/**
 * Contract for any API CRUD Adapter that integrates with an external API.
 *
 * Adapters are responsible for the actual communication layer and translation
 * of DTOs into API-specific formats and back.
 */
interface ApiCrudAdapterContract
{
    public function create(OutboundDTOContract $dto): InboundDTOContract;

    public function read(OutboundDTOContract $dto): ?InboundDTOContract;

    public function update(OutboundDTOContract $dto): InboundDTOContract;

    public function delete(OutboundDTOContract $dto): InboundDTOContract;

    /**
     * @return iterable<InboundDTOContract>
     */
    public function listAll(array $criteria = []): iterable;
}
