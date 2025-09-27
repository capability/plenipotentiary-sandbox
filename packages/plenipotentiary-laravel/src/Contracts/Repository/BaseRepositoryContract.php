<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryContract
{
    public function find(int|string $id): ?Model;

    public function findBy(array $criteria): ?Model;

    public function all(array $criteria = []): Collection;

    public function create(array $attributes): Model;

    public function update(int|string $id, array $attributes): Model;

    public function delete(int|string $id): bool;

    public function restore(int|string $id): bool;
}
