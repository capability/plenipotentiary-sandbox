<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Shared\Policies;

use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayCall;
use Plenipotentiary\Laravel\Pleni\Contracts\Policy\GatewayPolicy;
use Plenipotentiary\Laravel\Pleni\MCP\Shared\Support\AgentBudgetTracker;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Policy to enforce budget limits for agents
 */
final class AgentBudgetPolicy implements GatewayPolicy
{
    public function __construct(
        private AgentBudgetTracker $tracker,
    ) {}

    public function before(GatewayCall $call): GatewayCall
    {
        $agentId = $call->context['agent_id'] ?? 'default';

        if (! $this->tracker->canExecute($agentId)) {
            $usage = $this->tracker->getUsage($agentId);

            throw new \RuntimeException(
                "Agent '{$agentId}' has exceeded daily budget limit. ".
                "Used: \${$usage['daily_cost']}, Limit: \${$usage['limit']}"
            );
        }

        return $call;
    }

    public function after(GatewayCall $call, Result $result): Result
    {
        // Usage tracking happens in gateway after getting result
        return $result;
    }

    public function onError(GatewayCall $call, \Throwable|Result $error): Result
    {
        // Don't charge for errors
        return $error instanceof Result ? $error : Result::err($error);
    }
}
