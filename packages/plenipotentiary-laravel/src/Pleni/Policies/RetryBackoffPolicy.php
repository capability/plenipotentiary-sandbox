<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Policies;

use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicy;
use Plenipotentiary\Laravel\Support\Result;

final class RetryBackoffPolicy implements GatewayPolicy
{
    public function __construct(private int $maxRetries = 3, private int $backoffMs = 200) {}

    public function before(GatewayCall $call): GatewayCall
    {
        return $call;
    }

    public function after(GatewayCall $call, Result $result): Result
    {
        return $result;
    }

    public function onError(GatewayCall $call, \Throwable|Result $error): Result
    {
        static $attempts = 0;
        $attempts++;

        if ($attempts <= $this->maxRetries) {
            usleep($this->backoffMs * 1000 * $attempts);
            throw $error instanceof \Throwable ? $error : new \RuntimeException('Retry requested');
        }

        return $error instanceof Result ? $error : Result::err($error);
    }
}
