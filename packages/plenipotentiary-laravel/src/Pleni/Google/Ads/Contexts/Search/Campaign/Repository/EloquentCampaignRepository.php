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

    /**
     * Find a campaign by primary key.
     */
    public function find(int|string $id): ?Campaign
    {
        return $this->model->find($id);
    }

    /**
     * Override update to support persisting remote identifiers.
     *
     * @param int|string $id
     * @param array $attributes
     */
    public function update(int|string $id, array $attributes): Campaign
    {
        $instance = $this->find($id);
        if (!$instance) {
            throw new \RuntimeException("Campaign {$id} not found");
        }

        // Only allow updating fields we care about from remote
        $allowed = [
            'resource_id'   => $attributes['resource_id'] ?? null,
            'resource_name' => $attributes['resource_name'] ?? null,
            'name'          => $attributes['name'] ?? $instance->name,
        ];

        $instance->update(array_filter($allowed, fn ($v) => $v !== null));
        return $instance;
    }
}
