<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Support;

use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Exceptions\DomainException;
use Throwable;

/**
 * Maps Stripe API errors to domain exceptions.
 */
final class StripeErrorMapper implements ErrorMapperContract
{
    public function map(Throwable $exception): DomainException
    {
        return new DomainException(
            code: $exception->getCode(),
            message: $exception->getMessage(),
            httpStatus: 500,
            retryable: false,
        );
    }
}
