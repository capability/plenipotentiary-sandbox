<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Mapper;

use Plenipotentiary\Laravel\Contracts\Mapper\OutboundMapperContract;
use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;

class CampaignOutboundMapper implements OutboundMapperContract
{
    public function map(OutboundDTOContract $dto): array
    {
        return [
            'name' => $dto->name,
            // map using budgetMicros; BudgetManager will resolve to a resource when creating
            'budgetMicros' => $dto->budgetMicros,
            'advertisingChannelType' => $dto->advertisingChannelType,
            'customerId' => $dto->customerId,
            'id' => $dto->id,
        ];
    }
}
