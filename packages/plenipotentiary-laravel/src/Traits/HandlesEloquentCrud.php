<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Provides common Eloquent CRUD implementations.
 *
 * Intended to be used inside concrete Repositories implementing BaseRepositoryInterface.
 */
trait HandlesEloquentCrud
{
    protected Model $model;

    public function find(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findBy(array $criteria): ?Model
    {
        return $this->model->where($criteria)->first();
    }

    public function all(array $criteria = []): Collection
    {
        return $this->model->where($criteria)->get();
    }

    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function update(int|string $id, array $attributes): Model
    {
        $instance = $this->find($id);
        $instance->update($attributes);
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
}
