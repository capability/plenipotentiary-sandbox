<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Support\Result;
use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;

/**
 * Contract enforced by adapter operations so tooling can introspect INPUT_SPEC.
 *
 * @template TPayload of object
 */
interface AdapterVerbContract
{
    /**
     * @return array<string,array<string,mixed>> canonical field definitions
     */
    public static function inputSpec(): array;

    /**
     * @param  TPayload  $payload
     */
    public function perform(CanonicalDTOContract $dto, bool $validateOnly = false): Result;

    /**
     * Map a canonical DTO into a provider request object.
     */
    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): mixed;

    /**
     * Map a provider response back into a canonical DTO.
     *
     * @template TResponse
     * @template TDto
     * @param  TResponse  $response
     * @param  TDto  $dto
     * @return mixed
     */
    public function responseMapper(mixed $response, mixed $dto): CanonicalDTOContract;
}
