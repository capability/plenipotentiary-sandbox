<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Idempotency;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;

/**
 * Defines how to generate idempotency fingerprints for CRUD operations.
 */
interface IdempotencyHints
{
    public function fingerprintForCreate(CampaignCanonicalDTO $c): string;

    public function fingerprintForUpdate(CampaignCanonicalDTO $c): string;

    public function fingerprintForDelete(CampaignSelector $sel): string;
}
