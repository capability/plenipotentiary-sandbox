<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\Util\FieldMasks;
use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use InvalidArgumentException;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class UpdateOperation
{
    public function __construct(
        private ProviderClientContract $client,
        private LoggerInterface $logger,
    ) {}

    public function perform(CampaignCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        try {
            $this->spec($dto);
        } catch (ValidationException $e) {
            return Result::invalid($e->toArray());
        }

        try {
            $request = $this->requestMapper($dto, $validateOnly);
        } catch (InvalidArgumentException $e) {
            return Result::invalid([
                [
                    'field' => 'providerContext.google.customerId',
                    'rule' => 'required',
                    'message' => $e->getMessage(),
                ],
            ]);
        }

        $this->logger->info('Updating Google Ads campaign', [
            'resourceName' => $dto->resourceName(),
            'customerId' => GoogleAdsDefaults::apply($dto->providerContext())['google.customerId'] ?? null,
        ]);

        $response = $this->client->raw()
            ->getCampaignServiceClient()
            ->mutateCampaigns($request);

        if ($validateOnly) {
            return Result::ok();
        }

        $canonical = $this->responseMapper($response, $dto);

        return Result::ok($canonical);
    }

    public function spec(CampaignCanonicalDTO $dto): void
    {
        $violations = [];

        if (! $dto->resourceName()) {
            $violations[] = ['field' => 'resourceName', 'rule' => 'required', 'mapsTo' => 'campaign.resource_name'];
        }

        if (! $dto->name && ! $dto->status) {
            $violations[] = ['field' => '(name|status)', 'rule' => 'at least one updatable field required', 'mapsTo' => 'campaign'];
        }

        if ($violations) {
            throw ValidationException::fromArray('campaign.update', $violations);
        }
    }

    public function requestMapper(CampaignCanonicalDTO $dto, bool $validateOnly = false): MutateCampaignsRequest
    {
        $resourceName = $dto->resourceName();
        if (! $resourceName) {
            throw new InvalidArgumentException('Campaign update requires providerContext["resourceName"] or identifiers["resourceName"].');
        }

        $campaignPayload = ['resource_name' => $resourceName];
        if ($dto->name !== null) {
            $campaignPayload['name'] = $dto->name;
        }
        if ($dto->status !== null) {
            $campaignPayload['status'] = CampaignStatus::value($dto->status);
        }

        $campaign = new Campaign($campaignPayload);

        $operation = new CampaignOperation;
        $operation->setUpdate($campaign);
        $operation->setUpdateMask(FieldMasks::allSetFieldsOf($campaign));

        $context = GoogleAdsDefaults::require($dto->providerContext(), 'google.customerId');
        $dto->mergeProviderContext($context);
        $customerId = $context['google.customerId'];

        return (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setValidateOnly($validateOnly)
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public function responseMapper(MutateCampaignsResponse $response, CampaignCanonicalDTO $source): CampaignCanonicalDTO
    {
        $result = $response->getResults()[0] ?? null;
        $resource = $result?->getCampaign();
        $resourceName = $resource?->getResourceName() ?? $result?->getResourceName();

        return CampaignCanonicalDTO::fromArray([
            'externalId' => $resource ? (string) $resource->getId() : $source->externalId(),
            'name' => $resource?->getName() ?? $source->name,
            'status' => $resource?->getStatus() ?? $source->status,
            'budgetResourceName' => $resource?->getCampaignBudget() ?? $source->budgetResourceName,
            'identifiers' => array_filter([
                'resourceName' => $resourceName,
            ], fn ($value) => $value !== null && $value !== ''),
            'providerContext' => $source->providerContext(),
        ]);
    }
}
