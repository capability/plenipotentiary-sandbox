<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Operations\CallTool;

/**
 * Input DTO for MCP tool calls
 */
final class CallToolDTO
{
    public function __construct(
        public readonly string $server,
        public readonly string $tool,
        public readonly array $arguments = [],
        public readonly string $agentId = 'default',
        public readonly ?string $sessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            server: $data['server'],
            tool: $data['tool'],
            arguments: $data['arguments'] ?? [],
            agentId: $data['agentId'] ?? $data['agent_id'] ?? 'default',
            sessionId: $data['sessionId'] ?? $data['session_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'server' => $this->server,
            'tool' => $this->tool,
            'arguments' => $this->arguments,
            'agentId' => $this->agentId,
            'sessionId' => $this->sessionId,
        ];
    }
}
