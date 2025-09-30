<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Exceptions;

use Throwable;

final class TransportException extends DomainException
{
    public function __construct(string $message = 'Transport error.', bool $retryable = true, array $meta = [], ?Throwable $previous = null)
    {
        parent::__construct('TransportError', $message, 503, $retryable, $meta, $previous);
    }
}
