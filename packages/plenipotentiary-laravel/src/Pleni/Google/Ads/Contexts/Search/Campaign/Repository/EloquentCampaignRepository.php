<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository;

use App\Models\AcmeCart\Search\Campaign;
use Illuminate\Support\Collection;
use Plenipotentiary\Laravel\Traits\HandlesEloquentCrud;

final class EloquentCampaignRepository implements CampaignRepositoryContract
{
    use HandlesEloquentCrud;

    public function __construct(Campaign $model)
    {
        $this->model = $model;
    }

    public function findActive(): Collection
    {
        return $this->model->where('status', 'ACTIVE')->get();
    }

    public function findByExternalReference(string $externalRef): ?Campaign
    {
        return $this->model->where('external_ref', $externalRef)->first();
    }
}
