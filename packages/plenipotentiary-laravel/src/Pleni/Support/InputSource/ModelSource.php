<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Support\InputSource;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

final class ModelSource implements InputSource
{
    public function __construct(private Model $model) {}

    public function get(string $key): mixed
    {
        return Arr::get($this->model->toArray(), $key);
    }
}
