<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsRequest;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Budget\RequestMapper as BudgetRequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Spec as CreateSpec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\DeleteRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\DeleteResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\Spec as DeleteSpec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\UpdateRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\UpdateResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\Spec as UpdateSpec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelectorKind;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class CampaignApiCrudAdapter implements ApiCrudAdapterContract
{
    public function __construct(
        private ProviderClientContract $client,
        private CreateSpec $createSpec,
        private CreateRequestMapperContract $createRequestMapper,
        private CreateResponseMapperContract $createResponseMapper,
        private BudgetRequestMapper $budgetRequestMapper,
        private UpdateSpec $updateSpec,
        private UpdateRequestMapperContract $updateRequestMapper,
        private UpdateResponseMapperContract $updateResponseMapper,
        private DeleteSpec $deleteSpec,
        private DeleteRequestMapperContract $deleteRequestMapper,
        private DeleteResponseMapperContract $deleteResponseMapper,
        private ErrorMapperContract $errorMapper,
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
                'name' => $dto->name,
            ]);

            if ($validateOnly) {
                return Result::ok(); // validation passed, no side effects
            }

            // 5) Map response → canonical
            $canonical = $this->createResponseMapper->toCanonical($response);
            $canonical->mergeProviderContext($dto->providerContext());

            if (! $canonical->identifier('resourceName') && ($resourceName = $dto->resourceName())) {
                $canonical->identifiers['resourceName'] = $resourceName;
            }

            if (! $canonical->customerId() && $dto->customerId()) {
                $canonical->mergeProviderContext(['google.customerId' => $dto->customerId()]);
            }

            return Result::ok($canonical);

        } catch (\Plenipotentiary\Laravel\Support\Operation\ValidationException $e) {
            return Result::invalid($e->toArray());
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    public function update(CampaignCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        try {
            $this->updateSpec->preflight($dto);

            $request = $this->updateRequestMapper->toRequest($dto, $validateOnly);

            $this->logger->info('Updating Google Ads campaign', [
                'resourceName' => $dto->resourceName(),
                'customerId' => $dto->customerId(),
            ]);

            $response = $this->client->raw()
                ->getCampaignServiceClient()
                ->mutateCampaigns($request);

            if ($validateOnly) {
                return Result::ok();
            }

            $canonical = $this->updateResponseMapper->toCanonical($response);
            $canonical->mergeProviderContext($dto->providerContext());

            if (! $canonical->identifier('resourceName') && ($resourceName = $dto->resourceName())) {
                $canonical->identifiers['resourceName'] = $resourceName;
            }

            if (! $canonical->customerId() && $dto->customerId()) {
                $canonical->mergeProviderContext(['google.customerId' => $dto->customerId()]);
            }

            if (! $canonical->externalId() && $dto->externalId()) {
                $canonical->externalId = $dto->externalId();
            }

            return Result::ok($canonical);
        } catch (\Plenipotentiary\Laravel\Support\Operation\ValidationException $e) {
            return Result::invalid($e->toArray());
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    /**
     * Find a single campaign by selector (currently only ExternalId supported).
     */
    public function find(CampaignSelector $sel): Result
    {
        try {
            $cid = $sel->customerId();
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
    public function lookup(\Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup $criteria, string $customerId): Result
    {
        try {
            $mapper = new \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Read\LookupRequestMapper;
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

            $responseMapper = new \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Read\LookupResponseMapper;

            return Result::ok($responseMapper->toPage($resp));
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    public function delete(CampaignSelector $sel, bool $validateOnly = false): Result
    {
        try {
            $this->deleteSpec->preflight($sel);

            $customerId = $sel->providerContext()['google.customerId'] ?? GoogleAdsDefaults::get('google.customerId');

            if (! $customerId) {
                throw new \InvalidArgumentException('Delete operation requires google.customerId in providerContext or defaults.');
            }

            $request = $this->deleteRequestMapper->toDeleteRequest($customerId, $sel, $validateOnly);

            $this->logger->info('Deleting Google Ads campaign', [
                'selector_type' => $sel->type(),
                'selector_value' => $sel->value(),
                'customerId' => $customerId,
            ]);

            $response = $this->client->raw()
                ->getCampaignServiceClient()
                ->mutateCampaigns($request);

            if ($validateOnly) {
                return Result::ok();
            }

            $canonical = $this->deleteResponseMapper->toCanonical($response);
            $canonical->mergeProviderContext(array_merge(['google.customerId' => $customerId], $sel->providerContext()));

            return Result::ok($canonical);
        } catch (\Plenipotentiary\Laravel\Support\Operation\ValidationException $e) {
            return Result::invalid($e->toArray());
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }
}
