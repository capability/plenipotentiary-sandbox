<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\User;

use Plenipotentiary\Laravel\Contracts\CrudServiceContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\CampaignDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\External\CampaignExternalData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\Generated\CampaignService as GeneratedService;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\BudgetRequestBuilder;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\CampaignFinder;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\CampaignRequestBuilder;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\CampaignValidator;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsExceptionMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Translate\CampaignExternalToDomainMapper;

/**
 * User-level CampaignService.
 * Delegates to GeneratedService using composition, implements CrudServiceContract.
 *
 * Refactored to:
 * - centralise error handling in a private handleGoogleAdsException() helper
 * - add logging hooks for visibility (can wire to Laravel logger)
 * - use stronger validation of inputs
 *
 * Responsibilities:
 * - Implements CrudServiceContract for consistency.
 * - Delegates raw API interaction to GeneratedService.
 * - Adds domain mapping, orchestration, and error handling.
 * - Safe area for user customisation.
 */
class CampaignService implements CrudServiceContract
{
    public function __construct(
        protected GeneratedService $generated
    ) {}

    public function create(object $domainDto): object
    {
        \assert($domainDto instanceof CampaignDomainData);

        // Validate input
        CampaignValidator::validateForCreate($domainDto);

        // Idempotence: check if campaign already exists by name
        $existing = CampaignFinder::findByName(
            $this->generated->client(),
            $domainDto->customerId ?? '',
            $domainDto->name
        );
        if ($existing) {
            return CampaignExternalToDomainMapper::toDomain($existing);
        }

        try {
            $request = CampaignRequestBuilder::buildCreate(
                $domainDto->customerId ?? '',
                $domainDto
            );

            $response = $this->generated->client()
                ->getCampaignServiceClient()
                ->mutateCampaigns($request);

            $createdCampaign = $response->getResults()[0]->getCampaign();

            return CampaignExternalToDomainMapper::toDomain(
                CampaignExternalData::fromGoogleResponse($createdCampaign)
            );
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error creating campaign '{$domainDto->name}'");
        }
    }

    public function read(string|int $id): ?object
    {
        // Validate ID
        CampaignValidator::validateId($id);

        try {
            $external = $this->generated->getCampaign($id);

            if (! $external) {
                return null;
            }

            return CampaignExternalToDomainMapper::toDomain($external);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error reading campaign '$id'");
        }
    }

    public function update(object $domainDto): object
    {
        \assert($domainDto instanceof CampaignDomainData);

        // Validate input
        CampaignValidator::validateForUpdate($domainDto);

        try {
            $request = CampaignRequestBuilder::buildUpdate(
                $domainDto->customerId ?? '',
                $domainDto
            );

            $response = $this->generated->client()
                ->getCampaignServiceClient()
                ->mutateCampaigns($request);

            $updatedCampaign = $response->getResults()[0]->getCampaign();

            return CampaignExternalToDomainMapper::toDomain(
                CampaignExternalData::fromGoogleResponse($updatedCampaign)
            );
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error updating campaign '{$domainDto->resourceName}'");
        }
    }

    public function delete(string|int $id): bool
    {
        // Wrap given id into a Domain DTO so we can reuse validation/builders
        $dto = new CampaignDomainData(
            id: null,
            name: '',
            status: '',
            resourceName: (string) $id,
            customerId: null,
        );

        // Validate identifiers
        CampaignValidator::validateForDelete($dto);

        try {
            $request = CampaignRequestBuilder::buildRemove(
                $dto->customerId ?? '',
                $dto->resourceName
            );

            $response = $this->generated->client()
                ->getCampaignServiceClient()
                ->mutateCampaigns($request);

            return $response->getResults()->count() > 0;
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error removing campaign '{$dto->resourceName}'");
        }
    }

    /**
     * Creates (or finds) a shared budget for campaigns.
     * Note: intentionally kept in CampaignService to simplify usage,
     * since plenipotentiary avoids needing a separate BudgetService/DTO just for this.
     */
    public function createSharedBudget(CampaignDomainData $campaignData): ?CampaignExternalData
    {
        try {
            $budgetName = 'Shared Budget for Search Campaigns';

            $request = BudgetRequestBuilder::buildCreate(
                $campaignData->customerId ?? '',
                $budgetName,
                $campaignData->dailyBudget ?? 0
            );

            $response = $this->generated->client()
                ->getCampaignBudgetServiceClient()
                ->mutateCampaignBudgets($request);

            if ($response->getResults()->count() > 0) {
                $createdBudget = $response->getResults()[0]->getCampaignBudget();

                return CampaignExternalData::fromGoogleResponse($createdBudget);
            }

            return null;
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, 'Error creating shared budget');
        }
    }

    // Additional CRUD seam methods from CrudServiceContract
    public function listAll(CampaignDomainData $criteria): iterable
    {
        if (empty($criteria->customerId)) {
            throw new \InvalidArgumentException('Customer ID is required to list campaigns.');
        }
        $customerId = $criteria->customerId;

        try {
            $query = 'SELECT campaign.id, campaign.name, campaign.status, campaign.resource_name, campaign.campaign_budget FROM campaign';

            $searchRequest = new \Google\Ads\GoogleAds\V20\Services\SearchGoogleAdsRequest;
            $searchRequest->setCustomerId($customerId);
            $searchRequest->setQuery($query);

            $response = $this->generated->client()
                ->getGoogleAdsServiceClient()
                ->search($searchRequest);

            $results = [];
            foreach ($response->iterateAllElements() as $row) {
                $results[] = CampaignExternalToDomainMapper::toDomain(
                    CampaignExternalData::fromGoogleResponse($row->getCampaign())
                );
            }

            return $results;
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, 'Error listing campaigns');
        }
    }
}
