<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Mapper;

use App\Models\AcmeCart\Search\Campaign as LocalCampaign;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignOutboundDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsHelper;

/**
 * Mapper to convert a local Eloquent Campaign model
 * into a provider-agnostic CampaignOutboundDTO.
 */
final class LocalCampaignToOutboundMapper
{
    public static function map(LocalCampaign $campaign): CampaignOutboundDTO
    {
        return new CampaignOutboundDTO(
            name: $campaign->name,
            budgetMicros: GoogleAdsHelper::toMicros($campaign->daily_budget ?? GoogleAdsHelper::defaultCampaignConfig()['budget_amount']),
            advertisingChannelType: $campaign->channel_type ?? 'SEARCH',
            customerId: $campaign->customer_id ?: GoogleAdsHelper::linkedCustomerId(),
            id: $campaign->id
        );
    }
}
