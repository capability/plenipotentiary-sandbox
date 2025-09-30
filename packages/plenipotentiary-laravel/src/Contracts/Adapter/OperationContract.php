<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Support\Result;

/**
 * Contract enforced by adapter operations so tooling can introspect INPUT_SPEC.
 *
 * @template TPayload of object
 */
interface OperationContract
{
    /**
     * @return array<string,array<string,mixed>> canonical field definitions
     */
    public static function inputSpec(): array;

    /**
     * @param  TPayload  $payload
     */
    public function perform(object $payload, bool $validateOnly = false): Result;
}
