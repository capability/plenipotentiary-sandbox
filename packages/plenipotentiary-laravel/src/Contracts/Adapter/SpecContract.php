<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Pleni\Support\Operation\OperationDescription;
use Plenipotentiary\Laravel\Pleni\Support\Operation\ValidationException;

/**
 * Contract shared by all lifecycle Specs (Create/Update/Delete/Read).
 * 
 * Each Spec performs a cheap local validation before an API call,
 * and can describe its expected rules.
 */
interface SpecContract
{
    /**
     * Validate the given input object (DTO or Selector).
     *
     * @param mixed $input canonical DTO, selector, etc.
     * @throws ValidationException if violations are found
     */
    public function preflight(mixed $input): void;

    /**
     * Return a description of the validation rules.
     */
    public function describe(): OperationDescription;
}
