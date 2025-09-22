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
            'campaignBudget' => $dto->budgetMicros,
            'advertisingChannelType' => $dto->advertisingChannelType,
        ];
    }
}
