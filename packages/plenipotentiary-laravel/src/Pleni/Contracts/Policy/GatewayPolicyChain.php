<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Contracts\Policy;

use Plenipotentiary\Laravel\Support\Result;

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
