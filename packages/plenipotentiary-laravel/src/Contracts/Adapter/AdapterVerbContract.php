<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Contracts\Adapter;

use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;
use Plenipotentiary\Laravel\Support\Result;

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
     * Development helper - use during API exploration phase.
     * Converts raw array to DTO then calls perform().
     *
     * @deprecated Remove once INPUT_SPEC is finalized and use perform() directly
     * @param array<string,mixed> $input
     */
    public function performWithArray(array $input, bool $validateOnly = false): Result;

    /**
     * Map a canonical DTO into a provider request object.
     */
    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): mixed;

    /**
     * Map a provider response back into a canonical DTO.
     *
     * @template TResponse
     * @template TDto
     *
     * @param  TResponse  $response
     * @param  TDto  $dto
     * @return mixed
     */
    public function responseMapper(mixed $response, mixed $dto): CanonicalDTOContract;
}
