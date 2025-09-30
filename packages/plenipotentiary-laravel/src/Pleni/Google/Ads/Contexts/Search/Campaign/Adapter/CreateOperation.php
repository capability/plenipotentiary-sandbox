<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateOperation;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Budget\RequestMapper as BudgetRequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class CreateOperation
{
    public function __construct(
        private ProviderClientContract $client,
        private BudgetRequestMapper $budgetRequestMapper,
        private ErrorMapperContract $errorMapper,
        private LoggerInterface $logger,
    ) {}

    public function perform(CampaignCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        try {
            $this->spec($dto);

            [$request, $usesUnifiedMutate] = $this->requestMapper($dto, $validateOnly);

            $this->logger->info('Creating Google Ads campaign', [
                'name' => $dto->name,
                'customerId' => GoogleAdsDefaults::apply($dto->providerContext())['google.customerId'] ?? null,
                'usesUnifiedMutate' => $usesUnifiedMutate,
            ]);

            $response = $this->dispatch($request, $usesUnifiedMutate);

            if ($validateOnly) {
                return Result::ok();
            }

            $canonical = $this->responseMapper($response, $dto);

            return Result::ok($canonical);
        } catch (ValidationException $e) {
            return Result::invalid($e->toArray());
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    public function spec(CampaignCanonicalDTO $dto): void
    {
        $violations = [];

        if (! $dto->name || mb_strlen($dto->name) > 128) {
            $violations[] = ['field' => 'name', 'rule' => 'required|string|max:128', 'mapsTo' => 'campaign.name'];
        }

        if (! in_array($dto->status, ['ENABLED', 'PAUSED'], true)) {
            $violations[] = ['field' => 'status', 'rule' => 'enum[ENABLED,PAUSED]', 'mapsTo' => 'campaign.status'];
        }

        if (! $dto->budgetResourceName && $dto->budgetMicros === null) {
            $violations[] = ['field' => 'budgetResourceName', 'rule' => 'required unless budgetMicros provided', 'mapsTo' => 'campaign.campaign_budget'];
        }

        if ($violations) {
            throw ValidationException::fromArray('campaign.create', $violations);
        }
    }

    /**
     * @return array{0: MutateCampaignsRequest|MutateGoogleAdsRequest, 1: bool}
     */
    public function requestMapper(CampaignCanonicalDTO $dto, bool $validateOnly = false): array
    {
        $context = GoogleAdsDefaults::require($dto->providerContext(), 'google.customerId');
        $customerId = $context['google.customerId'];

        if ($dto->budgetResourceName) {
            $dto->mergeProviderContext($context);
            $campaign = new Campaign([
                'name' => $dto->name,
                'status' => CampaignStatus::value($dto->status ?? 'PAUSED'),
                'campaign_budget' => $dto->budgetResourceName,
            ]);

            $operation = (new CampaignOperation)->setCreate($campaign);

            $request = (new MutateCampaignsRequest)
                ->setCustomerId($customerId)
                ->setOperations([$operation])
                ->setValidateOnly($validateOnly)
                ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);

            return [$request, false];
        }

        $dto->mergeProviderContext($context);
        $budgetOp = $this->budgetRequestMapper->toBudgetOperation($dto, -1);
        $budgetResourceName = $budgetOp->getCreate()?->getResourceName();

        $campaign = new Campaign([
            'name' => $dto->name,
            'status' => CampaignStatus::value($dto->status ?? 'PAUSED'),
            'campaign_budget' => $budgetResourceName,
        ]);

        $campaignOp = (new CampaignOperation)->setCreate($campaign);

        $mutateOperations = [
            (new MutateOperation)->setCampaignBudgetOperation($budgetOp),
            (new MutateOperation)->setCampaignOperation($campaignOp),
        ];

        $request = (new MutateGoogleAdsRequest)
            ->setCustomerId($customerId)
            ->setMutateOperations($mutateOperations)
            ->setValidateOnly($validateOnly)
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);

        return [$request, true];
    }

    public function responseMapper(MutateCampaignsResponse|MutateGoogleAdsResponse $response, CampaignCanonicalDTO $source): CampaignCanonicalDTO
    {
        if ($response instanceof MutateCampaignsResponse) {
            $result = $response->getResults()[0] ?? null;
            $resource = $result?->getCampaign();

            return CampaignCanonicalDTO::fromArray([
                'externalId' => $resource ? (string) $resource->getId() : null,
                'name' => $resource?->getName(),
                'status' => $resource?->getStatus(),
                'budgetResourceName' => $resource?->getCampaignBudget(),
                'identifiers' => array_filter([
                    'resourceName' => $resource?->getResourceName() ?? $result?->getResourceName(),
                ], fn ($value) => $value !== null && $value !== ''),
                'providerContext' => $source->providerContext(),
            ]);
        }

        $resourceName = null;
        foreach ($response->getMutateOperationResponses() as $operationResponse) {
            if ($operationResponse->hasCampaignResult()) {
                $resourceName = $operationResponse->getCampaignResult()->getResourceName();
                break;
            }
        }

        return CampaignCanonicalDTO::fromArray([
            'name' => $source->name,
            'status' => $source->status,
            'budgetResourceName' => $source->budgetResourceName,
            'identifiers' => array_filter([
                'resourceName' => $resourceName,
            ], fn ($value) => $value !== null && $value !== ''),
            'providerContext' => $source->providerContext(),
        ]);
    }

    private function dispatch(MutateCampaignsRequest|MutateGoogleAdsRequest $request, bool $usesUnifiedMutate): MutateCampaignsResponse|MutateGoogleAdsResponse
    {
        $client = $this->client->raw();

        if ($usesUnifiedMutate) {
            return $client->getGoogleAdsServiceClient()->mutate($request);
        }

        return $client->getCampaignServiceClient()->mutateCampaigns($request);
    }
}
