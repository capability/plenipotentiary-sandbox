<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsRequest;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Read\LookupRequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Read\LookupResponseMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelectorKind;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class CampaignApiCrudAdapter implements ApiCrudAdapterContract
{
    public function __construct(
        private ProviderClientContract $client,
        private CreateOperation $createOperation,
        private UpdateOperation $updateOperation,
        private DeleteOperation $deleteOperation,
        private ErrorMapperContract $errorMapper,
        private LoggerInterface $logger,
    ) {}

    /**
     * Create a campaign. Set $validateOnly=true for dry-run validation.
     */
    public function create(CampaignCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        return $this->createOperation->perform($dto, $validateOnly);
    }

    public function update(CampaignCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        return $this->updateOperation->perform($dto, $validateOnly);
    }

    /**
     * Find a single campaign by selector (currently only ExternalId supported).
     */
    public function find(CampaignSelector $sel): Result
    {
        try {
            $context = GoogleAdsDefaults::require($sel->providerContext(), 'google.customerId');
            $cid = $context['google.customerId'];
            if ($sel->kind() !== CampaignSelectorKind::ExternalId) {
                throw new \InvalidArgumentException('Only ExternalId selector is supported for read()');
            }

            $query = sprintf(
                'SELECT campaign.resource_name, campaign.id, campaign.name, campaign.status, campaign.campaign_budget
                 FROM campaign
                 WHERE campaign.id = %d
                 LIMIT 1',
                (int) $sel->value()
            );

            $gaClient = $this->client->raw();
            $request = (new SearchGoogleAdsRequest)
                ->setCustomerId($cid)
                ->setQuery($query);

            $this->logger->info('Executing Google Ads campaign read', [
                'customerId' => $cid,
                'query' => $query,
            ]);

            $resp = $gaClient->getGoogleAdsServiceClient()->search($request);

            foreach ($resp->iterateAllElements() as $row) {
                $canonical = CampaignCanonicalDTO::fromArray([
                    'externalId' => (string) $row->getCampaign()->getId(),
                    'identifiers' => [
                        'resourceName' => $row->getCampaign()->getResourceName(),
                    ],
                    'name' => $row->getCampaign()->getName(),
                    'status' => $row->getCampaign()->getStatus(),
                    'budgetResourceName' => $row->getCampaign()->getCampaignBudget(),
                    'providerContext' => [
                        'google.customerId' => $cid,
                        'resourceName' => $row->getCampaign()->getResourceName(),
                    ],
                ]);

                return Result::ok($canonical);
            }

            return Result::ok(null);
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    /**
     * Lookup many campaigns by criteria.
     */
    public function lookup(Lookup $criteria, string $customerId): Result
    {
        try {
            $mapper = new LookupRequestMapper;
            $queryArr = $mapper->toQuery($customerId, $criteria);

            $gaClient = $this->client->raw();
            $request = (new SearchGoogleAdsRequest)
                ->setCustomerId($customerId)
                ->setQuery($queryArr['query']);

            $this->logger->info('Executing Google Ads campaign lookup', [
                'customerId' => $customerId,
                'query' => $queryArr['query'],
            ]);

            $resp = $gaClient->getGoogleAdsServiceClient()->search($request);

            $responseMapper = new LookupResponseMapper;

            return Result::ok($responseMapper->toPage($resp));
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    public function delete(CampaignSelector $sel, bool $validateOnly = false): Result
    {
        return $this->deleteOperation->perform($sel, $validateOnly);
    }
}
