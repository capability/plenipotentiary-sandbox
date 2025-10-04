<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Common\ManualCpc;
use Google\Ads\GoogleAds\V21\Enums\AdvertisingChannelTypeEnum\AdvertisingChannelType;
use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateSupport\CampaignCreateBudget;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class CampaignCreate implements AdapterVerbContract
{
    public const INPUT_SPEC = [
        'name' => [
            'rules' => ['required', 'string', 'min:1', 'max:128'],
        ],
        'status' => [
            'rules' => ['nullable', 'in:ENABLED,PAUSED,REMOVED'],
        ],
        'budgetMicros' => [
            'rules' => ['nullable', 'numeric', 'min:0'],
        ],
        'budgetResourceName' => [
            'rules' => ['nullable', 'string'],
        ],
        'providerContext.google.customerId' => [
            'rules' => ['required', 'string'],
            'source' => 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID',
        ],
    ];

    public function __construct(
        private ProviderClientContract $client,
        private LoggerInterface $logger,
        private CampaignCreateBudget $campaignCreateBudget,
    ) {}

    public static function inputSpec(): array
    {
        return self::INPUT_SPEC;
    }

    /**
     * Development helper - use during API exploration phase.
     * Converts raw array to DTO then calls perform().
     *
     * @deprecated Remove once INPUT_SPEC is finalized and use perform() directly
     */
    public function performWithArray(array $input, bool $validateOnly = false): Result
    {
        $dto = CampaignCanonicalDTO::fromArray($input);

        return $this->perform($dto, $validateOnly);
    }

    public function perform(CanonicalDTOContract $dto, bool $validateOnly = false): Result
    {

        if (! $dto->budgetResourceName) {

            // This context only ever uses a campaign budget. Spinning up a separate adapter would be overkill.
            // Plenipotentiary doesn’t mandate adapter structure — the developer decides.
            // Here we create the budget inline and set the resourceName, just like the SDK example
            $dto->budgetResourceName = $this->campaignCreateBudget->create($dto, (int) $dto->budgetMicros, $validateOnly);
        }

        $request = $this->requestMapper($dto, $validateOnly);

        $this->logger->info('Creating Google Ads campaign', [
            'customerId' => $dto->getProviderContextValue('google.customerId'),
            'name' => $dto->name,
            'validateOnly' => $validateOnly,
        ]);

        if ($validateOnly) {
            // In validateOnly mode, return the DTO as-is since no actual creation happens
            return Result::ok($dto);
        }

        $response = $this->client
            ->raw()
            ->getCampaignServiceClient()
            ->mutateCampaigns($request);

        $canonicalDto = $this->responseMapper($response, $dto);

        // Return both the canonical DTO AND the raw Google Ads response
        return Result::ok($canonicalDto, $response);
    }

    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): mixed
    {
        if (! $dto instanceof CampaignCanonicalDTO) {
            throw new \InvalidArgumentException('CampaignCreate::requestMapper expects CampaignCanonicalDTO');
        }

        $campaign = new Campaign([
            'name' => $dto->name,
            'status' => CampaignStatus::value($dto->status ?? 'PAUSED'),
            'advertising_channel_type' => AdvertisingChannelType::SEARCH,
            'manual_cpc' => new ManualCpc,
            'campaign_budget' => $dto->budgetResourceName,
        ]);

        $operation = (new CampaignOperation)->setCreate($campaign);

        return (new MutateCampaignsRequest)
            ->setCustomerId($dto->getProviderContextValue('google.customerId'))
            ->setOperations([$operation])
            ->setValidateOnly($validateOnly)
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
    }

    public function responseMapper(mixed $response, mixed $source): CanonicalDTOContract
    {
        if (! $response instanceof MutateCampaignsResponse || ! $source instanceof CampaignCanonicalDTO) {
            throw new \InvalidArgumentException('CampaignCreate::responseMapper expects (MutateCampaignsResponse, CampaignCanonicalDTO)');
        }

        $result = $response->getResults()[0] ?? null;
        $resource = $result?->getCampaign();

        $resourceName = $resource?->getResourceName() ?? $result?->getResourceName();

        $status = $resource?->getStatus();
        $statusName = $status !== null ? CampaignStatus::name($status) : $source->status;

        return CampaignCanonicalDTO::fromArray(array_filter([
            'externalId' => $resource ? (string) $resource->getId() : null,
            'name' => $resource?->getName() ?? $source->name,
            'status' => $statusName,
            'budgetResourceName' => $resource?->getCampaignBudget(),
            'providerContext' => $source->providerContext + ['resourceName' => $resourceName],
        ]));
    }
}
