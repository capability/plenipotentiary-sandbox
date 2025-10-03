<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Operations\CallTool;

use Plenipotentiary\Laravel\Pleni\MCP\Shared\Support\AgentBudgetTracker;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

/**
 * Gateway for MCP tool calls with agent safety policies
 *
 * This provides:
 * - Budget tracking
 * - Rate limiting (via policy chain)
 * - Audit logging
 * - Error handling
 * - Idempotency
 */
final class CallToolGateway
{
    public function __construct(
        private CallToolOperation $operation,
        private AgentBudgetTracker $budgetTracker,
        private LoggerInterface $logger,
    ) {}

    /**
     * Execute an MCP tool call with agent safety policies
     */
    public function call(CallToolDTO $dto, array $options = []): Result
    {
        $this->logger->info('Gateway: Agent calling MCP tool', [
            'agent_id' => $dto->agentId,
            'server' => $dto->server,
            'tool' => $dto->tool,
            'session_id' => $dto->sessionId,
        ]);

        // Check budget before execution
        if (! $this->budgetTracker->canExecute($dto->agentId)) {
            $usage = $this->budgetTracker->getUsage($dto->agentId);

            $this->logger->warning('Gateway: Agent budget exceeded', [
                'agent_id' => $dto->agentId,
                'usage' => $usage,
            ]);

            return Result::err([
                'error' => 'AGENT_BUDGET_EXCEEDED',
                'message' => "Agent '{$dto->agentId}' has exceeded daily budget",
                'usage' => $usage,
            ]);
        }

        // Execute operation
        $result = $this->operation->perform($dto);

        // Track usage after successful execution
        if ($result->isOk()) {
            $toolResult = $result->unwrap();
            $cost = $this->calculateCost($toolResult);

            $this->budgetTracker->recordUsage(
                agentId: $dto->agentId,
                operation: "{$dto->server}.{$dto->tool}",
                cost: $cost
            );

            $this->logger->info('Gateway: Tool call succeeded', [
                'agent_id' => $dto->agentId,
                'server' => $dto->server,
                'tool' => $dto->tool,
                'cost' => $cost,
            ]);
        } else {
            $this->logger->warning('Gateway: Tool call failed', [
                'agent_id' => $dto->agentId,
                'server' => $dto->server,
                'tool' => $dto->tool,
                'error' => $result->error(),
            ]);
        }

        return $result;
    }

    /**
     * Calculate cost for a tool call
     *
     * This is a simplified implementation. In production, you'd have
     * different pricing for different tools/servers.
     */
    private function calculateCost(CallToolResult $result): float
    {
        // Base cost per tool call
        $cost = 0.01;

        // Add token-based cost if available (for LLM tools)
        if ($tokenCount = $result->meta['token_count'] ?? null) {
            $cost += ($tokenCount / 1000) * 0.002; // $0.002 per 1K tokens
        }

        // Add duration-based cost
        if ($duration = $result->meta['duration_ms'] ?? null) {
            $cost += ($duration / 1000) * 0.001; // $0.001 per second
        }

        // Add size-based cost (for file operations)
        if ($size = $result->meta['file_size'] ?? null) {
            $cost += ($size / 1024 / 1024) * 0.0001; // $0.0001 per MB
        }

        return round($cost, 4);
    }

    /**
     * List available tools from an MCP server
     */
    public function listTools(string $serverName): Result
    {
        // This would call the operation layer to query available tools
        // Simplified for now
        return Result::ok(
            CallToolResult::fromArray([
                'server' => $serverName,
                'tool' => 'list_tools',
                'content' => [],
                'agentId' => 'system',
            ])
        );
    }
}
