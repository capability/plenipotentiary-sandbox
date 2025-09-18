<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Traits;

use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignExternalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Exceptions\GoogleAdsExceptionMapper;

/**
 * Trait providing the concrete Google Ads SDK call to read Campaigns.
 */
trait ReadsCampaign
{
    abstract protected function adsClient(): GoogleAdsClient;

    /**
     * Fetch a campaign by its resource name or ID.
     */
    protected function doRead(string|int $id): ?CampaignDomainDTO
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Campaign ID/resource name is required');
        }

        try {
            $service = $this->adsClient()->getCampaignServiceClient();
            $response = $service->getCampaign((string) $id);

            if (! $response) {
                return null;
            }

            // Convert the GoogleAds Campaign resource directly to ExternalDTO
            $external = new CampaignExternalDTO(
                resourceName: $response->getResourceName(),
                campaignId: $response->getId(),
                name: $response->getName(),
                status: $response->getStatus(),
                customerId: $this->extractCustomerIdFromResource($response->getResourceName()),
                dailyBudget: null,
                budgetResourceName: $response->getCampaignBudget(),
            );

            // Map ExternalDTO → DomainDTO (inline instead of using mapper class)
            return new CampaignDomainDTO(
                id: null,
                name: $external->name,
                status: (string) $external->status,
                customerId: $external->customerId,
                resourceName: $external->resourceName,
                dailyBudget: $external->dailyBudget,
                campaignId: $external->campaignId,
                budgetResourceName: $external->budgetResourceName,
            );
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, "Error reading campaign '$id'");
        }
    }

    /**
     * Extract customer ID from resource name string: customers/{customerId}/campaigns/{id}
     */
    private function extractCustomerIdFromResource(string $resourceName): string
    {
        if (preg_match('#customers/([^/]+)/campaigns/#', $resourceName, $matches)) {
            return $matches[1];
        }
        return '';
    }
}
