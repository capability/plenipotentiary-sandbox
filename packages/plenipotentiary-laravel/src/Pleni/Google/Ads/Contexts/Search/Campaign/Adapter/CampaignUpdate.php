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
use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Support\InputSpecValidator;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class CampaignUpdate implements AdapterVerbContract
{
    public const INPUT_SPEC = [
        'name' => [
            'rules' => ['nullable', 'string', 'min:1', 'max:128'],
        ],
        'status' => [
            'rules' => ['nullable', 'in:ENABLED,PAUSED,REMOVED'],
        ],
        'providerContext.google.customerId' => [
            'rules' => ['required', 'string'],
            'source' => 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID',
        ],
        'providerContext.resourceName' => [
            'rules' => ['required', 'string'],
        ],
    ];

    public function __construct(
        private ProviderClientContract $client,
        private LoggerInterface $logger,
    ) {}

    public static function inputSpec(): array
    {
        return self::INPUT_SPEC;
    }

    public function perform(CanonicalDTOContract $dto, bool $validateOnly = false): Result
    {
        if (! $dto instanceof CampaignCanonicalDTO) {
            throw new \InvalidArgumentException('CampaignUpdate expects CampaignCanonicalDTO');
        }

        $request = $this->requestMapper($dto, $validateOnly);

        $this->logger->info('Updating Google Ads campaign', [
            'resourceName' => $dto->getProviderContextValue('resourceName'),
            'customerId' => $dto->getProviderContextValue('google.customerId'),
        ]);

        $response = $this->client->raw()
            ->getCampaignServiceClient()
            ->mutateCampaigns($request);

        return $validateOnly
            ? Result::ok()
            : Result::ok($this->responseMapper($response, $dto));
    }

    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): mixed
    {

        $resourceName = $dto->getProviderContextValue('resourceName');
        if (! $resourceName) {
            throw new InvalidArgumentException('Campaign update requires providerContext["resourceName"].');
        }

        $campaignPayload = ['resource_name' => $resourceName];
        if ($dto->name !== null) {
            $campaignPayload['name'] = $dto->name;
        }
        if ($dto->status !== null) {
            $campaignPayload['status'] = CampaignStatus::value($dto->status);
        }

        // If no mutable fields were provided, return invalid
        if ($dto->name === null && $dto->status === null) {
            return new MutateCampaignsRequest(); // placeholder, perform() will catch via preflight
        }

        $campaign = new Campaign($campaignPayload);

        $operation = new CampaignOperation;
        $operation->setUpdate($campaign);
        $operation->setUpdateMask(FieldMasks::allSetFieldsOf($campaign));

        $context = GoogleAdsDefaults::require($dto->providerContext, 'google.customerId');
        $dto->setProviderContext($context);
        $customerId = $context['google.customerId'];

        return (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setValidateOnly($validateOnly)
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public function responseMapper(mixed $response, mixed $source): CanonicalDTOContract
    {
        if (! $response instanceof MutateCampaignsResponse || ! $source instanceof CampaignCanonicalDTO) {
            throw new InvalidArgumentException('CampaignUpdate::responseMapper expects (MutateCampaignsResponse, CampaignCanonicalDTO)');
        }

        $result = $response->getResults()[0] ?? null;
        $resource = $result?->getCampaign();
        $resourceName = $resource?->getResourceName() ?? $result?->getResourceName();

        $status = $resource?->getStatus();
        $statusName = $status !== null ? CampaignStatus::name($status) : $source->status;

        return CampaignCanonicalDTO::fromArray(array_filter([
            'externalId' => $resource ? (string) $resource->getId() : $source->externalId,
            'name' => $resource?->getName() ?? $source->name,
            'status' => $statusName,
            'budgetResourceName' => $resource?->getCampaignBudget() ?? $source->budgetResourceName,
            'providerContext' => $source->providerContext + ['resourceName' => $resourceName],
        ]));
    }

}
