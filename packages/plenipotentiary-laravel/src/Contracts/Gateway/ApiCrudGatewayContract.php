<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Gateway;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Lookup\Lookup;
use Plenipotentiary\Laravel\Pleni\Support\Result;

/**
 * Provider-agnostic gateway contract for CRUD operations.
 * 
 * Acts as the central entry point where logging, jobs, and events can hook in.
 * Delegates to a provider-specific ApiCrudAdapterContract behind the scenes.
 */
interface ApiCrudGatewayContract
{
    public function create(CampaignCanonicalDTO $c, bool $validateOnly = false): Result;

    public function find(CampaignSelector $sel): Result;

    public function lookup(Lookup $criteria, string $customerId): Result;

    public function update(CampaignCanonicalDTO $c, bool $validateOnly = false): Result;

    public function delete(CampaignSelector $sel, bool $validateOnly = false): Result;
}
