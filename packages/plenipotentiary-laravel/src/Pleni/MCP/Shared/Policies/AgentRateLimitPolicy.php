<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Shared\Policies;

use Illuminate\Cache\RateLimiter;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicy;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Policy to rate limit agent tool calls
 */
final class AgentRateLimitPolicy implements GatewayPolicy
{
    public function __construct(
        private RateLimiter $limiter,
        private int $maxAttempts = 100,
        private int $decayMinutes = 1,
    ) {}

    public function before(GatewayCall $call): GatewayCall
    {
        $agentId = $call->context['agent_id'] ?? 'default';
        $key = "agent:{$agentId}:rate_limit";

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = $this->limiter->availableIn($key);

            throw new \RuntimeException(
                "Agent '{$agentId}' exceeded rate limit ({$this->maxAttempts} calls per minute). ".
                "Try again in {$seconds} seconds."
            );
        }

        $this->limiter->hit($key, $this->decayMinutes * 60);

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
