<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Policies;

use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicy;
use Plenipotentiary\Laravel\Support\Result;

final class MetricsPolicy implements GatewayPolicy
{
    private array $counters = [];

    public function before(GatewayCall $call): GatewayCall
    {
        $this->counters[$call->operation]['started'] = ($this->counters[$call->operation]['started'] ?? 0) + 1;
        return $call;
    }

    public function after(GatewayCall $call, Result $result): Result
    {
        $this->counters[$call->operation]['succeeded'] = ($this->counters[$call->operation]['succeeded'] ?? 0) + 1;
        return $result;
    }

    public function onError(GatewayCall $call, \Throwable|Result $error): Result
    {
        $this->counters[$call->operation]['failed'] = ($this->counters[$call->operation]['failed'] ?? 0) + 1;
        return $error instanceof Result ? $error : Result::err($error);
    }

    public function counters(): array
    {
        return $this->counters;
    }
}
