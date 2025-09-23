<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway;

use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\DTO\ContextualInboundDTOContract;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;

class CampaignApiCrudGateway implements ApiCrudGatewayContract
{
    public function __construct(
        private ApiCrudAdapterContract $adapter,
    ) {}

    public function create(OutboundDTOContract $dto): ContextualInboundDTOContract
    {
        return $this->adapter->create($dto);
    }

    public function read(OutboundDTOContract $dto): ?ContextualInboundDTOContract
    {
        return $this->adapter->read($dto);
    }

    public function update(OutboundDTOContract $dto): ContextualInboundDTOContract
    {
        return $this->adapter->update($dto);
    }

    public function delete(OutboundDTOContract $dto): ContextualInboundDTOContract
    {
        return $this->adapter->delete($dto);
    }

    public function listAll(array $criteria = []): iterable
    {
        return $this->adapter->listAll($criteria);
    }
}
