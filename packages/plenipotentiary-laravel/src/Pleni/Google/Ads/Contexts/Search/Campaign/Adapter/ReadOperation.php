<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Services\SearchGoogleAdsRequest;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector\CampaignSelectorKind;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class ReadOperation
{
    public function __construct(
        private ProviderClientContract $client,
        private LoggerInterface $logger,
    ) {}

    public function perform(CampaignSelector $selector): Result
    {
        try {
            $context = $this->spec($selector);
        } catch (ValidationException $e) {
            return Result::invalid($e->toArray());
        }

        $request = $this->requestMapper($selector, $context['google.customerId']);

        $this->logger->info('Executing Google Ads campaign read', [
            'customerId' => $context['google.customerId'],
            'selector' => $selector->value(),
        ]);

        $response = $this->client->raw()
            ->getGoogleAdsServiceClient()
            ->search($request);

        $campaign = $this->responseMapper($response, $context['google.customerId']);

        return Result::ok($campaign);
    }

    /**
     * @return array{google.customerId:string}
     */
    public function spec(CampaignSelector $selector): array
    {
        $violations = [];

        if ($selector->value() === '') {
            $violations[] = ['field' => 'selector.value', 'rule' => 'required', 'mapsTo' => 'campaign.id'];
        }

        if ($selector->kind() !== CampaignSelectorKind::ExternalId) {
            $violations[] = ['field' => 'selector.kind', 'rule' => 'ExternalId only', 'mapsTo' => 'campaign.id'];
        }

        $context = GoogleAdsDefaults::apply($selector->providerContext());
        $customerId = $context['google.customerId'] ?? null;
        if (! $customerId) {
            $violations[] = ['field' => 'providerContext.google.customerId', 'rule' => 'required', 'mapsTo' => 'customerId'];
        }

        if ($violations) {
            throw ValidationException::fromArray('campaign.read', $violations);
        }

        return ['google.customerId' => $customerId];
    }

    private function requestMapper(CampaignSelector $selector, string $customerId): SearchGoogleAdsRequest
    {
        $query = sprintf(
            'SELECT campaign.resource_name, campaign.id, campaign.name, campaign.status, campaign.campaign_budget
             FROM campaign
             WHERE campaign.id = %d
             LIMIT 1',
            (int) $selector->value()
        );

        return (new SearchGoogleAdsRequest)
            ->setCustomerId($customerId)
            ->setQuery($query);
    }

    private function responseMapper(object $response, string $customerId): ?CampaignCanonicalDTO
    {
        foreach ($response->iterateAllElements() as $row) {
            $campaign = $row->getCampaign();

            return CampaignCanonicalDTO::fromArray([
                'externalId' => (string) $campaign->getId(),
                'identifiers' => [
                    'resourceName' => $campaign->getResourceName(),
                ],
                'name' => $campaign->getName(),
                'status' => $campaign->getStatus(),
                'budgetResourceName' => $campaign->getCampaignBudget(),
                'providerContext' => [
                    'google.customerId' => $customerId,
                    'resourceName' => $campaign->getResourceName(),
                ],
            ]);
        }

        return null;
    }
}
