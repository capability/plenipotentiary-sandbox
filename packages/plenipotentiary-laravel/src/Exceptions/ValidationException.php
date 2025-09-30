<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Exceptions;

use Throwable;

final class ValidationException extends DomainInvalidException
{
    public function __construct(array $violations, array $meta = [], ?Throwable $previous = null)
    {
        parent::__construct($violations, $meta, $previous);
    }
}
