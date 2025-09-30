<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Exceptions;

use Throwable;

final class AuthException extends DomainException
{
    public function __construct(string $message = 'Authentication failed.', array $meta = [], ?Throwable $previous = null)
    {
        parent::__construct('AuthFailed', $message, 401, false, $meta, $previous);
    }
}
