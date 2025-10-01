<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository;

use App\Models\AcmeCart\Search\Campaign;
use Illuminate\Support\Collection;

use Illuminate\Database\Eloquent\Relations\HasMany;

final class EloquentCampaignRepository implements CampaignRepositoryContract
{
    private Campaign $model;

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

    public function find(int|string $id): ?Campaign
    {
        return $this->model->find($id);
    }

    public function all(array $criteria = []): Collection
    {
        return $this->model->where($criteria)->get();
    }

    public function create(array $attributes): Campaign
    {
        return $this->model->create($attributes);
    }

    public function update(int|string $id, array $attributes): ?Campaign
    {
        $instance = $this->find($id);
        if ($instance) {
            $instance->update($attributes);
        }

        return $instance;
    }

    public function delete(int|string $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    public function restore(int|string $id): bool
    {
        return (bool) $this->model->withTrashed()->find($id)?->restore();
    }

    public function findWithBudgets(string $id): array
    {
        $campaign = $this->model->with('budgets')->findOrFail($id);

        return [
            'campaign' => $campaign,
            'budgets' => $campaign->budgets ?? collect(),
        ];
    }
}

