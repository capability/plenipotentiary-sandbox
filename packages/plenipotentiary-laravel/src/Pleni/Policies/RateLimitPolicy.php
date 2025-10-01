<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Policies;

use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicy;
use Plenipotentiary\Laravel\Support\Result;

final class RateLimitPolicy implements GatewayPolicy
{
    private array $tokens = [];
    public function __construct(private int $limitPerMinute = 60) {}

    public function before(GatewayCall $call): GatewayCall
    {
        $key = $call->operation;
        $now = time();
        $window = intdiv($now, 60);

        $this->tokens[$key][$window] = ($this->tokens[$key][$window] ?? 0) + 1;
        if ($this->tokens[$key][$window] > $this->limitPerMinute) {
            throw new \RuntimeException("Rate limit exceeded for {$call->operation}");
        }

        return $call;
    }

    public function after(GatewayCall $call, Result $result): Result
    {
        return $result;
    }

    public function onError(GatewayCall $call, \Throwable|Result $error): Result
    {
        return $error instanceof Result ? $error : Result::err($error);
    }
}
