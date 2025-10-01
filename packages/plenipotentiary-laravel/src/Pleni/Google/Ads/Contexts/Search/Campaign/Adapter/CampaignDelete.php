<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use InvalidArgumentException;
use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class CampaignDelete implements AdapterVerbContract
{
    public const INPUT_SPEC = [
        'externalId' => [
            'rules' => ['nullable', 'string'],
        ],
        'providerContext.google.customerId' => [
            'rules' => ['required', 'string'],
            'source' => 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID',
        ],
        'providerContext.resourceName' => [
            'rules' => ['nullable', 'string'],
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

        $customerId = $dto->getProviderContextValue('google.customerId');

        $request = $this->requestMapper($dto, $validateOnly);

        $this->logger->info('Deleting Google Ads campaign', [
            'externalId' => $dto->externalId,
            'resourceName' => $dto->getProviderContextValue('resourceName'),
            'customerId' => $customerId,
        ]);

        $response = $this->client->raw()
            ->getCampaignServiceClient()
            ->mutateCampaigns($request);

        if ($validateOnly) {
            return Result::ok();
        }

        return Result::ok($this->responseMapper($response, $dto));
    }

    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): mixed
    {
        if (! $dto instanceof CampaignCanonicalDTO) {
            throw new InvalidArgumentException('CampaignDelete::requestMapper expects CampaignCanonicalDTO');
        }

        $context = GoogleAdsDefaults::require($dto->providerContext, 'google.customerId');
        $dto->setProviderContext($context);

        $customerId = $context['google.customerId'];
        $resourceName = $dto->getProviderContextValue('resourceName');

        $finalResource = $resourceName ?: $this->deriveResourceName($dto, $customerId);

        $operation = new CampaignOperation;
        $operation->setRemove($finalResource);

        return (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setValidateOnly($validateOnly);
    }

    private function deriveResourceName(CampaignCanonicalDTO $dto, string $customerId): string
    {
        $externalId = $dto->externalId;

        if (! $externalId) {
            throw new InvalidArgumentException('Campaign delete requires externalId when providerContext.resourceName is absent.');
        }

        return sprintf('customers/%s/campaigns/%s', $customerId, $externalId);
    }

    public function responseMapper(mixed $response, mixed $source): CanonicalDTOContract
    {
        if (! $response instanceof MutateCampaignsResponse || ! $source instanceof CampaignCanonicalDTO) {
            throw new InvalidArgumentException('CampaignDelete::responseMapper expects (MutateCampaignsResponse, CampaignCanonicalDTO)');
        }

        $result = $response->getResults()[0] ?? null;
        $resourceName = $result?->getResourceName();

        return CampaignCanonicalDTO::fromArray([
            'externalId' => $source->externalId,
            'providerContext' => $source->providerContext + ['resourceName' => $resourceName],
        ]);
    }
}
