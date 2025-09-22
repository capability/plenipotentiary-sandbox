<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Gateway;

/**
 * Contract for a CRUD API Gateway providing standard campaign operations.
 *
 * The ApiCrudGateway is the entrypoint to interact with external APIs
 * that support create, read, update, delete, and listAll operations.
 */
use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;

interface ApiCrudGatewayContract
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
