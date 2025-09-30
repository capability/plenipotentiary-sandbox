<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Tests\Stubs\Idempotency;

use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Key\CampaignSelector;

final class FakeCampaignIdempotencyHints implements IdempotencyHints
{
    public function fingerprintForCreate(CampaignCanonicalDTO $c): string
    {
        return 'create:'.($c->name ?? '');
    }

    public function fingerprintForUpdate(CampaignCanonicalDTO $c): string
    {
        return 'update:'.($c->resourceName() ?? $c->externalId() ?? '');
    }

    public function fingerprintForDelete(CampaignSelector $sel): string
    {
        return 'delete:'.$sel->value();
    }
}
