<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Shared\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Tracks agent budget/cost for MCP tool calls
 */
final class AgentBudgetTracker
{
    public function __construct(
        private array $budgetConfig = [],
    ) {}

    /**
     * Check if agent can execute (has budget remaining)
     */
    public function canExecute(string $agentId): bool
    {
        $usage = $this->getUsage($agentId);
        $limit = $this->getDailyLimit($agentId);

        return $usage['daily_cost'] < $limit;
    }

    /**
     * Record usage for an agent
     */
    public function recordUsage(string $agentId, string $operation, float $cost): void
    {
        $key = $this->getCacheKey($agentId);
        $usage = Cache::get($key, ['daily_cost' => 0.0, 'call_count' => 0, 'operations' => []]);

        $usage['daily_cost'] += $cost;
        $usage['call_count']++;
        $usage['operations'][] = [
            'operation' => $operation,
            'cost' => $cost,
            'timestamp' => now()->toIso8601String(),
        ];

        // Store for 24 hours (resets daily)
        Cache::put($key, $usage, now()->addDay());
    }

    /**
     * Get current usage for an agent
     */
    public function getUsage(string $agentId): array
    {
        $key = $this->getCacheKey($agentId);

        return Cache::get($key, [
            'daily_cost' => 0.0,
            'call_count' => 0,
            'operations' => [],
            'limit' => $this->getDailyLimit($agentId),
            'remaining' => $this->getDailyLimit($agentId),
        ]);
    }

    /**
     * Reset usage for an agent
     */
    public function reset(string $agentId): void
    {
        Cache::forget($this->getCacheKey($agentId));
    }

    /**
     * Get daily budget limit for an agent
     */
    private function getDailyLimit(string $agentId): float
    {
        // Check agent-specific budget
        if (isset($this->budgetConfig[$agentId]['daily_limit'])) {
            return (float) $this->budgetConfig[$agentId]['daily_limit'];
        }

        // Fall back to default
        return (float) ($this->budgetConfig['default']['daily_limit'] ?? 10.00);
    }

    /**
     * Get cache key for agent usage
     */
    private function getCacheKey(string $agentId): string
    {
        $date = now()->format('Y-m-d');

        return "agent_budget:{$agentId}:{$date}";
    }
}
