<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Contracts\Policy;

use Plenipotentiary\Laravel\Support\Result;

interface GatewayPolicy
{
    /**
     * Called before the adapter call. May block (e.g., rate limit) or wrap context.
     */
    public function before(GatewayCall $call): GatewayCall;

    /**
     * Called after a successful adapter call. May augment meta/telemetry only.
     */
    public function after(GatewayCall $call, Result $result): Result;

    /**
     * Called when adapter throws or returns failure. May transform error or decide retry.
     */
    public function onError(GatewayCall $call, \Throwable|Result $error): Result;
}
