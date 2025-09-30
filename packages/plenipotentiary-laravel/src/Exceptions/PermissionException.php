<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Exceptions;

use Throwable;

final class PermissionException extends DomainException
{
    public function __construct(string $message = 'Permission denied.', array $meta = [], ?Throwable $previous = null)
    {
        parent::__construct('PermissionDenied', $message, 403, false, $meta, $previous);
    }
}
