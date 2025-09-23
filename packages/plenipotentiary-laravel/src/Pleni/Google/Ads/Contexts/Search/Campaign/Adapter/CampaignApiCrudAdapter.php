<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\DTO\ContextualInboundDTOContract;
use Plenipotentiary\Laravel\Contracts\Mapper\OutboundMapperContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Google\Ads\GoogleAds\V20\Services\{
    CampaignOperation,
    MutateCampaignsRequest,
    SearchGoogleAdsRequest
};
use Google\Ads\GoogleAds\V20\Resources\Campaign;
use Google\Ads\GoogleAds\V20\Enums\{
    CampaignStatusEnum\CampaignStatus,
    AdvertisingChannelTypeEnum\AdvertisingChannelType,
    ResponseContentTypeEnum\ResponseContentType
};
use Google\Ads\GoogleAds\V20\Common\ManualCpc;
use Google\Ads\GoogleAds\Util\V20\FieldMasks;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\AdapterSupport\BudgetManager;
use Psr\Log\LoggerInterface;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\AdapterSupport\CampaignApiInboundDTOMapper;

class CampaignApiCrudAdapter implements ApiCrudAdapterContract
{
    public function __construct(
        private ProviderClientContract $client,
        private OutboundMapperContract $outboundMapper,
        private ErrorMapperContract $errorMapper,
        private LoggerInterface $logger,
    ) {}

    public function create(OutboundDTOContract $dto): ContextualInboundDTOContract
    {
        try {
            // Ensure budget exists or create one
            $budgetManager = new BudgetManager($this->client);
            $budgetResourceName = $budgetManager->ensureSharedBudget($dto);

            // Build Campaign create operation
            $operation = (new CampaignOperation())->setCreate(
                (new Campaign())
                    ->setName($dto->name)
                    ->setStatus(CampaignStatus::PAUSED)
                    ->setCampaignBudget($budgetResourceName)
                    ->setAdvertisingChannelType(AdvertisingChannelType::SEARCH)
                    ->setManualCpc(new ManualCpc())
            );

            $request = (new MutateCampaignsRequest())
                ->setCustomerId($dto->customerId)
                ->setOperations([$operation])
                ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);

            $gaClient = $this->client->raw();

            $this->logger->info('Creating Google Ads campaign', [
                'customerId' => $dto->customerId,
                'name'       => $dto->name,
            ]);

            $response = $gaClient->getCampaignServiceClient()->mutateCampaigns($request);

            $remoteCampaign = $response->getResults()[0]->getCampaign();

            $this->logger->debug('Created Google Ads campaign', [
                'id'           => $remoteCampaign->getId(),
                'resourceName' => $remoteCampaign->getResourceName(),
                'status'       => $remoteCampaign->getStatus(),
            ]);

            // Use dedicated mapper for Campaign objects
            return CampaignApiInboundDTOMapper::fromCampaignObject($remoteCampaign);
        } catch (\Throwable $e) {
            $this->logger->error('Error creating campaign', ['message' => $e->getMessage()]);
            throw $this->errorMapper->map($e);
        }
    }

    public function read(OutboundDTOContract $dto): ?ContextualInboundDTOContract
    {
        try {
            if (!property_exists($dto, 'id') || empty($dto->id)) {
                throw new \InvalidArgumentException(
                    'Campaign id is required for a read operation.'
                );
            }

			$query = sprintf(
			    'SELECT
campaign.accessible_bidding_strategy,
campaign.ad_serving_optimization_status,
campaign.advertising_channel_sub_type,
campaign.advertising_channel_type,
campaign.app_campaign_setting.app_id,
campaign.app_campaign_setting.app_store,
campaign.app_campaign_setting.bidding_strategy_goal_type,
campaign.asset_automation_settings,
campaign.audience_setting.use_audience_grouped,
campaign.base_campaign,
campaign.bidding_strategy,
campaign.bidding_strategy_system_status,
campaign.bidding_strategy_type,
campaign.brand_guidelines.accent_color,
campaign.brand_guidelines.main_color,
campaign.brand_guidelines.predefined_font_family,
campaign.brand_guidelines_enabled,
campaign.campaign_budget,
campaign.campaign_group,
campaign.commission.commission_rate_micros,
campaign.contains_eu_political_advertising,
campaign.demand_gen_campaign_settings.upgraded_targeting,
campaign.dynamic_search_ads_setting.domain_name,
campaign.dynamic_search_ads_setting.language_code,
campaign.dynamic_search_ads_setting.use_supplied_urls_only,
campaign.end_date,
campaign.excluded_parent_asset_field_types,
campaign.excluded_parent_asset_set_types,
campaign.experiment_type,
campaign.final_url_suffix,
campaign.fixed_cpm.goal,
campaign.fixed_cpm.target_frequency_info.target_count,
campaign.fixed_cpm.target_frequency_info.time_unit,
campaign.frequency_caps,
campaign.geo_target_type_setting.negative_geo_target_type,
campaign.geo_target_type_setting.positive_geo_target_type,
campaign.hotel_property_asset_set,
campaign.hotel_setting.hotel_center_id,
campaign.id,
campaign.keyword_match_type,
campaign.labels,
campaign.listing_type,
campaign.local_campaign_setting.location_source_type,
campaign.local_services_campaign_settings.category_bids,
campaign.manual_cpa,
campaign.manual_cpc.enhanced_cpc_enabled,
campaign.manual_cpm,
campaign.manual_cpv,
campaign.maximize_conversion_value.target_roas,
campaign.maximize_conversions.target_cpa_micros,
campaign.name,
campaign.network_settings.target_content_network,
campaign.network_settings.target_google_search,
campaign.network_settings.target_google_tv_network,
campaign.network_settings.target_partner_search_network,
campaign.network_settings.target_search_network,
campaign.network_settings.target_youtube,
campaign.optimization_goal_setting.optimization_goal_types,
campaign.optimization_score,
campaign.payment_mode,
campaign.percent_cpc.cpc_bid_ceiling_micros,
campaign.percent_cpc.enhanced_cpc_enabled,
campaign.performance_max_upgrade.performance_max_campaign,
campaign.performance_max_upgrade.pre_upgrade_campaign,
campaign.performance_max_upgrade.status,
campaign.pmax_campaign_settings.brand_targeting_overrides.ignore_exclusions_for_shopping_ads,
campaign.primary_status,
campaign.primary_status_reasons,
campaign.real_time_bidding_setting.opt_in,
campaign.resource_name,
campaign.selective_optimization.conversion_actions,
campaign.serving_status,
campaign.shopping_setting.advertising_partner_ids,
campaign.shopping_setting.campaign_priority,
campaign.shopping_setting.disable_product_feed,
campaign.shopping_setting.enable_local,
campaign.shopping_setting.feed_label,
campaign.shopping_setting.merchant_id,
campaign.shopping_setting.use_vehicle_inventory,
campaign.start_date,
campaign.status,
campaign.target_cpa.cpc_bid_ceiling_micros,
campaign.target_cpa.cpc_bid_floor_micros,
campaign.target_cpa.target_cpa_micros,
campaign.target_cpm.target_frequency_goal.target_count,
campaign.target_cpm.target_frequency_goal.time_unit,
campaign.target_cpv,
campaign.target_impression_share.cpc_bid_ceiling_micros,
campaign.target_impression_share.location,
campaign.target_impression_share.location_fraction_micros,
campaign.target_roas.cpc_bid_ceiling_micros,
campaign.target_roas.cpc_bid_floor_micros,
campaign.target_roas.target_roas,
campaign.target_spend.cpc_bid_ceiling_micros,
campaign.target_spend.target_spend_micros,
campaign.targeting_setting.target_restrictions,
campaign.tracking_setting.tracking_url,
campaign.tracking_url_template,
campaign.travel_campaign_settings.travel_account_id,
campaign.url_custom_parameters,
campaign.url_expansion_opt_out,
campaign.vanity_pharma.vanity_pharma_display_url_mode,
campaign.vanity_pharma.vanity_pharma_text,
campaign.video_brand_safety_suitability,
campaign.video_campaign_settings.video_ad_format_control.format_restriction,
campaign.video_campaign_settings.video_ad_format_control.non_skippable_in_stream_restrictions.max_duration,
campaign.video_campaign_settings.video_ad_format_control.non_skippable_in_stream_restrictions.min_duration,
campaign.video_campaign_settings.video_ad_inventory_control.allow_in_feed,
campaign.video_campaign_settings.video_ad_inventory_control.allow_in_stream,
campaign.video_campaign_settings.video_ad_inventory_control.allow_shorts
FROM campaign
WHERE campaign.id = %d',
(int) $dto->id
			);
			
            

            $gaClient = $this->client->raw();
            $request = (new SearchGoogleAdsRequest())
                ->setCustomerId($dto->customerId)
                ->setQuery($query);

            $this->logger->info('Executing Google Ads campaign read', [
                'customerId' => $dto->customerId,
                'query' => $query,
            ]);

            $response = $gaClient->getGoogleAdsServiceClient()->search($request);

            foreach ($response->iterateAllElements() as $row) {
                $campaign = $row->getCampaign();

                $this->logger->debug('Mapping Google Ads campaign', [
                    'id_type' => gettype($campaign->getId()),
                    'id_value' => $campaign->getId(),
                    'resourceName' => $campaign->getResourceName(),
                ]);

                return CampaignApiInboundDTOMapper::fromSearchRow($row);
            }

            return null;
        } catch (\Throwable $e) {
            throw $this->errorMapper->map($e);
        }
    }

    public function update(OutboundDTOContract $dto): ContextualInboundDTOContract
    {
        try {
            if (!property_exists($dto, 'resourceName') || empty($dto->resourceName)) {
                throw new \InvalidArgumentException(
                    'Campaign resource name is required for an update operation.'
                );
            }

            $campaign = new Campaign([
                'resource_name' => $dto->resourceName,
                'name' => $dto->name,
                'status' => CampaignStatus::value($dto->status),
            ]);

            $fieldMask = FieldMasks::fromSet($campaign);

            $operation = new CampaignOperation();
            $operation->setUpdate($campaign);
            $operation->setUpdateMask($fieldMask);

            $request = (new MutateCampaignsRequest())
                ->setCustomerId($dto->customerId)
                ->setOperations([$operation])
                ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);

            $gaClient = $this->client->raw();

            $this->logger->info('Updating Google Ads campaign', [
                'customerId'   => $dto->customerId,
                'resourceName' => $dto->resourceName,
            ]);

            $response = $gaClient->getCampaignServiceClient()->mutateCampaigns($request);

            $updatedCampaign = $response->getResults()[0]->getCampaign();

            $this->logger->debug('Updated Google Ads campaign', [
                'id'           => $updatedCampaign->getId(),
                'resourceName' => $updatedCampaign->getResourceName(),
                'status'       => $updatedCampaign->getStatus(),
            ]);

            // Use dedicated mapper for Campaign objects
            return CampaignApiInboundDTOMapper::fromCampaignObject($updatedCampaign);
        } catch (\Throwable $e) {
            $this->logger->error('Error updating campaign', ['message' => $e->getMessage()]);
            throw $this->errorMapper->map($e);
        }
    }

    public function delete(OutboundDTOContract $dto): ContextualInboundDTOContract
    {
        try {
            if (!property_exists($dto, 'resourceName') || empty($dto->resourceName)) {
                throw new \InvalidArgumentException(
                    'Campaign resource name is required for a delete operation.'
                );
            }

            $operation = new CampaignOperation();
            $operation->setRemove($dto->resourceName);

            $request = (new MutateCampaignsRequest())
                ->setCustomerId($dto->customerId)
                ->setOperations([$operation]);

            $gaClient = $this->client->raw();

            $this->logger->info('Deleting Google Ads campaign', [
                'customerId'   => $dto->customerId,
                'resourceName' => $dto->resourceName,
            ]);

            $response = $gaClient->getCampaignServiceClient()->mutateCampaigns($request);

            $removedResult = $response->getResults()[0];

            $this->logger->debug('Deleted Google Ads campaign', [
                'resourceName' => $removedResult->getResourceName(),
            ]);

            return CampaignApiInboundDTOMapper::fromMutateResponse([$removedResult], 'remove')[0];
        } catch (\Throwable $e) {
            $this->logger->error('Error deleting campaign', ['message' => $e->getMessage()]);
            throw $this->errorMapper->map($e);
        }
    }

    public function listAll(array $criteria = []): iterable
    {
        try {
            $query = 'SELECT campaign.id, campaign.resource_name, campaign.name, campaign.status FROM campaign';

            $gaClient = $this->client->raw();
            $request = (new SearchGoogleAdsRequest())
                ->setCustomerId($gaClient->getLoginCustomerId())
                ->setQuery($query);

            $this->logger->info('Listing Google Ads campaigns', [
                'loginCustomerId' => $gaClient->getLoginCustomerId(),
            ]);

            $response = $gaClient->getGoogleAdsServiceClient()->search($request);

            $results = [];
            foreach ($response->iterateAllElements() as $row) {
                $campaign = $row->getCampaign();

                $results[] = CampaignApiInboundDTOMapper::fromSearchRow($row);
            }

            $this->logger->debug('Listed campaigns count', ['count' => count($results)]);

            return $results;
        } catch (\Throwable $e) {
            $this->logger->error('Error listing campaigns', ['message' => $e->getMessage()]);
            throw $this->errorMapper->map($e);
        }
    }
}
