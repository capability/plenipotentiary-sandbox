<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsRequest;
use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\InputSpecValidator;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class CampaignRead implements AdapterVerbContract
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

        $customerId = (string) $dto->getProviderContextValue('google.customerId');

        // Ensure at least one identifier (externalId or resourceName) is present
        if (empty($dto->externalId) && empty($dto->getProviderContextValue('resourceName'))) {
            return Result::invalid([['field' => 'externalId|providerContext.resourceName', 'message' => 'At least one identifier required']], self::INPUT_SPEC);
        }

        $request = $this->requestMapper($dto, $validateOnly);

        $this->logger->info('Executing Google Ads campaign read', [
            'customerId' => $customerId,
            'externalId' => $dto->externalId,
            'resourceName' => $dto->getProviderContextValue('resourceName'),
        ]);

        $response = $this->client->raw()
            ->getGoogleAdsServiceClient()
            ->search($request);

        $campaign = $this->responseMapper($response, $dto);

        return Result::ok($campaign);
    }

    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): mixed
    {
        if (! $dto instanceof CampaignCanonicalDTO) {
            throw new \InvalidArgumentException('CampaignRead::requestMapper expects CampaignCanonicalDTO');
        }

        $customerId = (string) $dto->getProviderContextValue('google.customerId');
        $resourceName = $dto->getProviderContextValue('resourceName');

        if ($resourceName) {
            $query = sprintf(
                'SELECT campaign.resource_name, campaign.id, campaign.name, campaign.status, campaign.campaign_budget
                 FROM campaign
                 WHERE campaign.resource_name = "%s"
                 LIMIT 1',
                addslashes($resourceName)
            );
        } else {
            $externalId = (int) $dto->externalId;
            $query = sprintf(
                'SELECT campaign.resource_name, campaign.id, campaign.name, campaign.status, campaign.campaign_budget
                 FROM campaign
                 WHERE campaign.id = %d
                 LIMIT 1',
                $externalId
            );
        }

        return (new SearchGoogleAdsRequest)
            ->setCustomerId($customerId)
            ->setQuery($query);
    }

    public function responseMapper(mixed $response, mixed $source): CanonicalDTOContract
    {
        if (! is_object($response) || ! $source instanceof CampaignCanonicalDTO) {
            throw new InvalidArgumentException('CampaignRead::responseMapper expects iterable response and CampaignCanonicalDTO');
        }

        $customerId = $source->getProviderContextValue('google.customerId');

        $found = null;
        foreach ($response->iterateAllElements() as $row) {
            $campaign = $row->getCampaign();

            $status = $campaign->getStatus();

            $found = CampaignCanonicalDTO::fromArray([
                'externalId' => (string) $campaign->getId(),
                'name' => $campaign->getName(),
                'status' => $status !== null ? CampaignStatus::name($status) : null,
                'budgetResourceName' => $campaign->getCampaignBudget(),
                'providerContext' => array_filter([
                    'google.customerId' => $customerId,
                    'resourceName' => $campaign->getResourceName(),
                ], static fn ($value) => $value !== null && $value !== ''),
            ]);
        }

        if (! $found) {
            return Result::invalid([['field' => 'externalId|providerContext.resourceName', 'message' => 'No matching campaign found']], self::INPUT_SPEC);
        }

        return $found;
    }

}
