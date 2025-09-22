<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Mapper;

use Plenipotentiary\Laravel\Contracts\Mapper\InboundMapperContract;
use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignInboundDTO;

class CampaignInboundMapper implements InboundMapperContract
{
    public function map(array $payload): InboundDTOContract
    {
        return new CampaignInboundDTO(
            id: $payload['id'],
            resourceName: $payload['resourceName'],
            status: $payload['status'],
        );
    }
}
