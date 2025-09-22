<?php

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO;

use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsHelper;

readonly class CampaignOutboundDTO implements OutboundDTOContract
{
    public function __construct(
        public ?string $name,
        public int $budgetMicros,
        public ?string $advertisingChannelType,
        public string $customerId,
        public string|int|null $id = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'budgetMicros' => $this->budgetMicros,
            'advertisingChannelType' => $this->advertisingChannelType,
            'customerId' => $this->customerId,
            'id' => $this->id,
        ];
    }

    public static function newWithDefaults(
        ?string $name,
        int $budgetMicros,
        ?string $advertisingChannelType,
        ?string $customerId,
        string|int|null $id = null
    ): self {
        return new self(
            name: $name,
            budgetMicros: $budgetMicros,
            advertisingChannelType: $advertisingChannelType,
            customerId: $customerId ?: GoogleAdsHelper::linkedCustomerId(),
            id: $id,
        );
    }
}
