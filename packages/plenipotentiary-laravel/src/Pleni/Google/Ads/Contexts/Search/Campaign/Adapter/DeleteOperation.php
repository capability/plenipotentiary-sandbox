<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use InvalidArgumentException;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector\CampaignSelectorKind;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\Operation\ValidationException;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class DeleteOperation
{
    public function __construct(
        private ProviderClientContract $client,
        private LoggerInterface $logger,
    ) {}

    public function perform(CampaignSelector $selector, bool $validateOnly = false): Result
    {
        try {
            $this->spec($selector);
        } catch (ValidationException $e) {
            return Result::invalid($e->toArray());
        }

        try {
            $context = GoogleAdsDefaults::require($selector->providerContext(), 'google.customerId');
        } catch (InvalidArgumentException $e) {
            return Result::invalid([
                [
                    'field' => 'providerContext.google.customerId',
                    'rule' => 'required',
                    'message' => $e->getMessage(),
                ],
            ]);
        }

        $customerId = $context['google.customerId'];
        $request = $this->requestMapper($customerId, $selector, $validateOnly);

        $this->logger->info('Deleting Google Ads campaign', [
            'selector_type' => $selector->type(),
            'selector_value' => $selector->value(),
            'customerId' => $customerId,
        ]);

        $response = $this->client->raw()
            ->getCampaignServiceClient()
            ->mutateCampaigns($request);

        if ($validateOnly) {
            return Result::ok();
        }

        $canonical = $this->responseMapper($response, $selector, $customerId, $context);

        return Result::ok($canonical);
    }

    public function spec(CampaignSelector $selector): void
    {
        if (! $selector->value()) {
            throw ValidationException::fromArray('campaign.delete', [
                ['field' => 'selector', 'rule' => 'required', 'mapsTo' => 'campaign.resource_name or id'],
            ]);
        }
    }

    public function requestMapper(string $customerId, CampaignSelector $selector, bool $validateOnly = false): MutateCampaignsRequest
    {
        $context = $selector->providerContext();
        $resourceName = $context['resourceName']
            ?? ($selector->kind() === CampaignSelectorKind::ResourceName
                ? $selector->value()
                : sprintf('customers/%s/campaigns/%s', $customerId, $selector->value()));

        $operation = new CampaignOperation;
        $operation->setRemove($resourceName);

        return (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setValidateOnly($validateOnly);
    }

    public function responseMapper(
        MutateCampaignsResponse $response,
        CampaignSelector $selector,
        string $customerId,
        array $context
    ): CampaignCanonicalDTO {
        $result = $response->getResults()[0] ?? null;
        $resourceName = $result?->getResourceName();

        return CampaignCanonicalDTO::fromArray([
            'identifiers' => array_filter([
                'resourceName' => $resourceName,
            ], fn ($value) => $value !== null && $value !== ''),
            'providerContext' => array_merge($context, $selector->providerContext()),
        ]);
    }
}
