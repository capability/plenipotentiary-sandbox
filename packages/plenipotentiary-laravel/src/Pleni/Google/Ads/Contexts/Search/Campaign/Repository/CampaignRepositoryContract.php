<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository;

use App\Models\AcmeCart\Search\Campaign;
use Illuminate\Support\Collection;
use Plenipotentiary\Laravel\Contracts\Repository\BaseRepositoryContract;

interface CampaignRepositoryContract extends BaseRepositoryContract
{
    public function findActive(): Collection;

    public function findByExternalReference(string $externalRef): ?Campaign;
}
