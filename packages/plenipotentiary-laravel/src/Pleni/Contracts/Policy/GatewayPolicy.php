<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Contracts\Policy;

use Plenipotentiary\Laravel\Support\Result;

final class GatewayCall
{
    public function __construct(
        public readonly string $operation,
        public readonly array $payload = [],
        public readonly array $options = [],
        public readonly array $context = [],
        public readonly object|null $hints = null
    ) {}
}

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

final class GatewayPolicyChain
{
    /** @param array<GatewayPolicy> $policies ordered */
    public function __construct(private array $policies) {}

    public function invoke(callable $adapterCall, GatewayCall $call): Result
    {
        $c = $call;
        foreach ($this->policies as $p) {
            $c = $p->before($c);
        }

        try {
            $res = $adapterCall($c); // Adapter returns Result
            foreach (array_reverse($this->policies) as $p) {
                $res = $p->after($c, $res);
            }
            return $res;
        } catch (\Throwable $e) {
            $err = $e;
            foreach (array_reverse($this->policies) as $p) {
                $err = $p->onError($c, $err);
            }
            return $err instanceof Result ? $err : Result::err($e);
        }
    }
}
