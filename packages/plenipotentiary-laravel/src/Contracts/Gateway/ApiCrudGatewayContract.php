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
use Plenipotentiary\Laravel\Contracts\DTO\ContextualInboundDTOContract;

interface ApiCrudGatewayContract
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
