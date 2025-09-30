<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Exceptions;

use Throwable;

/**
 * Represents user-fixable validation issues surfaced by providers.
 */
class DomainInvalidException extends DomainException
{
    /**
     * @param  array<int,array<string,mixed>>  $violations
     * @param  array<string,mixed>  $meta
     */
    public function __construct(
        private readonly array $violations,
        array $meta = [],
        ?Throwable $previous = null
    ) {
        parent::__construct('InvalidInput', 'Invalid input', 422, false, $meta, $previous);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function violations(): array
    {
        return $this->violations;
    }

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize() + ['violations' => $this->violations];
    }
}
