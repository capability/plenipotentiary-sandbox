<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\DTO\OutboundDTOContract;
use Plenipotentiary\Laravel\Contracts\DTO\InboundDTOContract;
use Plenipotentiary\Laravel\Contracts\Mapper\OutboundMapperContract;
use Plenipotentiary\Laravel\Contracts\Mapper\InboundMapperContract;
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

class CampaignApiCrudAdapter implements ApiCrudAdapterContract
{
    public function __construct(
        private ProviderClientContract $client,
        private OutboundMapperContract $outboundMapper,
        private InboundMapperContract $inboundMapper,
        private ErrorMapperContract $errorMapper,
    ) {}

    public function create(OutboundDTOContract $dto): InboundDTOContract
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
            $response = $gaClient->getCampaignServiceClient()->mutateCampaigns($request);

            $remoteCampaign = $response->getResults()[0]->getCampaign();

            return $this->inboundMapper->map([
                'id' => $remoteCampaign->getId(),
                'resourceName' => $remoteCampaign->getResourceName(),
                'status' => $remoteCampaign->getStatus(),
            ]);
        } catch (\Throwable $e) {
            throw $this->errorMapper->map($e);
        }
    }

    public function read(OutboundDTOContract $dto): ?InboundDTOContract
    {
        try {
            if (!property_exists($dto, 'id') || empty($dto->id)) {
                throw new \InvalidArgumentException(
                    'Campaign id is required for a read operation.'
                );
            }

            $query = sprintf(
                'SELECT campaign.id, campaign.resource_name, campaign.name, campaign.status FROM campaign WHERE campaign.id = %d',
                (int) $dto->id
            );

            $gaClient = $this->client->raw();
            $request = (new SearchGoogleAdsRequest())
                ->setCustomerId($dto->customerId)
                ->setQuery($query);
            $response = $gaClient->getGoogleAdsServiceClient()->search($request);

            foreach ($response->iterateAllElements() as $row) {
                $campaign = $row->getCampaign();

                return $this->inboundMapper->map([
                    'id' => $campaign->getId(),
                    'resourceName' => $campaign->getResourceName(),
                    'status' => $campaign->getStatus(),
                    'name' => $campaign->getName(),
                ]);
            }

            return null;
        } catch (\Throwable $e) {
            throw $this->errorMapper->map($e);
        }
    }

    public function update(OutboundDTOContract $dto): InboundDTOContract
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
            $response = $gaClient->getCampaignServiceClient()->mutateCampaigns($request);

            $updatedCampaign = $response->getResults()[0]->getCampaign();

            return $this->inboundMapper->map([
                'id' => $updatedCampaign->getId(),
                'resourceName' => $updatedCampaign->getResourceName(),
                'status' => $updatedCampaign->getStatus(),
            ]);
        } catch (\Throwable $e) {
            throw $this->errorMapper->map($e);
        }
    }

    public function delete(OutboundDTOContract $dto): InboundDTOContract
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
            $response = $gaClient->getCampaignServiceClient()->mutateCampaigns($request);

            $removedResult = $response->getResults()[0];

            return $this->inboundMapper->map([
                'resourceName' => $removedResult->getResourceName(),
            ]);
        } catch (\Throwable $e) {
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
            $response = $gaClient->getGoogleAdsServiceClient()->search($request);

            $results = [];
            foreach ($response->iterateAllElements() as $row) {
                $campaign = $row->getCampaign();

                $results[] = $this->inboundMapper->map([
                    'id' => $campaign->getId(),
                    'resourceName' => $campaign->getResourceName(),
                    'status' => $campaign->getStatus(),
                    'name' => $campaign->getName(),
                ]);
            }

            return $results;
        } catch (\Throwable $e) {
            throw $this->errorMapper->map($e);
        }
    }
}
