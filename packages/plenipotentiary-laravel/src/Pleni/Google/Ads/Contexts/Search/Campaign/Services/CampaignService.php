<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Services;

use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Traits\CreatesCampaign;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignDomainDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignExternalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Exceptions\GoogleAdsExceptionMapper;

use Plenipotentiary\Laravel\Contracts\ApiCrudServiceContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Traits\ReadsCampaign;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Traits\UpdatesCampaign;

/**
 * Public CampaignService implementing the CRUD-style contract.
 */
class CampaignService implements ApiCrudServiceContract
{
    use CreatesCampaign;
    use ReadsCampaign;
    use UpdatesCampaign;

    public function __construct(
        protected GoogleAdsClient $client,
    ) {}

    protected function adsClient(): GoogleAdsClient
    {
        return $this->client;
    }

    /**
     * Create: ExternalDTO → DomainDTO.
     */
    public function create(object $domainDto): object
    {
        \assert($domainDto instanceof CampaignExternalDTO);
        try {
            return $this->doCreate($domainDto);
        } catch (\Throwable $e) {
            throw GoogleAdsExceptionMapper::map($e, 'Error creating campaign');
        }
    }

    /**
     * Read a campaign by ID.
     */
    public function read(string|int $id): ?object
    {
        return $this->doRead($id);
    }

    /**
     * Update a campaign.
     */
    public function update(object $domainDto): object
    {
        \assert($domainDto instanceof CampaignExternalDTO);
        return $this->doUpdate($domainDto);
    }

    /**
     * Delete a campaign by ID.
     */
    public function delete(string|int $id): bool
    {
        // TODO integrate real delete logic
        return false;
    }

    /**
     * List all campaigns.
     */
    public function listAll(): iterable
    {
        // TODO integrate real list logic
        return [];
    }

    /**
     * Search campaigns by criteria.
     */
    public function searchByCriteria(array $criteria): iterable
    {
        // TODO integrate real search logic
        return [];
    }
}
