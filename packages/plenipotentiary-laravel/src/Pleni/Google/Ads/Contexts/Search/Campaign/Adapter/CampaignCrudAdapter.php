<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\CrudAdapterContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Selector\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup;
use Plenipotentiary\Laravel\Support\Result;

final class CampaignCrudAdapter implements CrudAdapterContract
{
    public function __construct(
        private CampaignCreate $createOperation,
        private CampaignUpdate $updateOperation,
        private CampaignDelete $deleteOperation,
        private CampaignRead $readOperation,
        private CampaignReadMany $readManyOperation,
    ) {}

    /**
     * Create a campaign. Set $validateOnly=true for dry-run validation.
     */
    public function create(CampaignCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        return $this->createOperation->perform($dto, $validateOnly);
    }

    public function update(CampaignCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        return $this->updateOperation->perform($dto, $validateOnly);
    }

    /**
     * Find a single campaign by selector (currently only ExternalId supported).
     */
    public function find(CampaignSelector $sel): Result
    {
        return $this->readOperation->perform($sel->toCanonicalDTO());
    }

    public function lookup(Lookup $criteria, string $customerId): Result
    {
        return $this->readManyOperation->perform($criteria, $customerId);
    }

    public function delete(CampaignSelector $sel, bool $validateOnly = false): Result
    {
        return $this->deleteOperation->perform($sel->toCanonicalDTO(), $validateOnly);
    }
}
