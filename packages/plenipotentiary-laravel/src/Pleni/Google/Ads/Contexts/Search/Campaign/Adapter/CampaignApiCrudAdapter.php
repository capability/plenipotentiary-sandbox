<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Services\CampaignServiceClient;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Support\Result;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Spec as CreateSpec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Budget\RequestMapper as BudgetRequestMapper;

use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsRequest;
use Psr\Log\LoggerInterface;

final class CampaignApiCrudAdapter implements ApiCrudAdapterContract
{
    public function __construct(
        private ProviderClientContract $client,
        private CreateSpec $createSpec,
        private CreateRequestMapperContract $createRequestMapper,
        private CreateResponseMapperContract $createResponseMapper,
        private ErrorMapperContract $errorMapper,
        private BudgetRequestMapper $budgetRequestMapper,
        private LoggerInterface $logger,
    ) {}


    /**
     * Create a campaign. Set $validateOnly=true for dry-run validation.
     */
    public function create(CampaignCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        try {
            // 1) Cheap local checks
            $this->createSpec->preflight($dto);

            // 2) Ensure budget, if you’re creating budgets on the fly
            if (empty($dto->budgetResourceName)) {
                // Use negative temporary id, e.g. -1
                $budgetOp = $this->budgetRequestMapper->toBudgetOperation($dto, -1);

                // Build unified request instead of campaigns-only
                $request = $this->createRequestMapper->toUnifiedRequest($dto, $validateOnly, $budgetOp);
            } else {
                // 3) Build provider request (mapper derives and sets customerId from DTO)
                $request = $this->createRequestMapper->toCampaignsRequest($dto, $validateOnly);
            }

            // 4) Single RPC, either validate-only or execute
            $ga = $this->client->raw();

            if ($dto->budgetResourceName) {
                $response = $ga->getCampaignServiceClient()->mutateCampaigns($request);
            } else {
                $response = $ga->getGoogleAdsServiceClient()->mutate($request);
            }

            $this->logger->info('Creating Google Ads campaign', [
                'name'       => $dto->name,
            ]);

            if ($validateOnly) {
                return Result::ok(); // validation passed, no side effects
            }

            // 5) Map response → canonical
            return Result::ok($this->createResponseMapper->toCanonical($response));

        } catch (\Plenipotentiary\Laravel\Pleni\Support\Operation\ValidationException $e) {
            return Result::invalid($e->toArray());
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    // TODO: Refactor: remove old OutboundDTOContract CRUD methods.
    // Use new Spec + RequestMapper + ResponseMapper approach for create/read/update/delete.

    /**
     * Find a single campaign by selector (currently only ExternalId supported).
     */
    public function find(\Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector $sel): Result
    {
        try {
            $cid = $sel->customerId();
            if ($sel->kind() !== \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelectorKind::ExternalId) {
                throw new \InvalidArgumentException('Only ExternalId selector is supported for read()');
            }

            $query = sprintf(
                "SELECT campaign.resource_name, campaign.id, campaign.name, campaign.status, campaign.campaign_budget
                 FROM campaign
                 WHERE campaign.id = %d
                 LIMIT 1",
                (int) $sel->value()
            );

            $gaClient = $this->client->raw();
            $request = (new SearchGoogleAdsRequest())
                ->setCustomerId($cid)
                ->setQuery($query);

            $this->logger->info('Executing Google Ads campaign read', [
                'customerId' => $cid,
                'query'      => $query,
            ]);

            $resp = $gaClient->getGoogleAdsServiceClient()->search($request);

            foreach ($resp->iterateAllElements() as $row) {
                $canonical = new CampaignCanonicalDTO();
                $canonical->accountKeys['google.customerId'] = $cid;
                $canonical->externalId        = (string) $row->getCampaign()->getId();
                $canonical->identifiers['resourceName'] = $row->getCampaign()->getResourceName();
                $canonical->name              = $row->getCampaign()->getName();
                $canonical->status            = $row->getCampaign()->getStatus();
                $canonical->budgetResourceName= $row->getCampaign()->getCampaignBudget();
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
    public function lookup(\Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup $criteria, string $customerId): Result
    {
        try {
            $mapper = new \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Read\LookupRequestMapper();
            $queryArr = $mapper->toQuery($customerId, $criteria);

            $gaClient = $this->client->raw();
            $request = (new SearchGoogleAdsRequest())
                ->setCustomerId($customerId)
                ->setQuery($queryArr['query']);

            $this->logger->info('Executing Google Ads campaign lookup', [
                'customerId' => $customerId,
                'query'      => $queryArr['query'],
            ]);

            $resp = $gaClient->getGoogleAdsServiceClient()->search($request);

            $responseMapper = new \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Read\LookupResponseMapper();
            return Result::ok($responseMapper->toPage($resp));
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }
}
