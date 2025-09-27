<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository;

use Plenipotentiary\Laravel\Contracts\Repository\BaseRepositoryContract;
use Illuminate\Support\Collection;
use App\Models\AcmeCart\Search\Campaign;

interface CampaignRepositoryContract extends BaseRepositoryContract
{
    public function findActive(): Collection;

    public function findByExternalReference(string $externalRef): ?Campaign;
}
