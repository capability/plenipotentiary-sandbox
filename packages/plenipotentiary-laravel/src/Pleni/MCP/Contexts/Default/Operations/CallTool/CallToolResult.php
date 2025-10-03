<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Operations\CallTool;

use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;

/**
 * Result DTO for MCP tool calls
 */
final class CallToolResult implements CanonicalDTOContract
{
    public function __construct(
        public readonly string $server,
        public readonly string $tool,
        public readonly mixed $content,
        public readonly string $contentType = 'text/plain',
        public readonly array $meta = [],
        public readonly string $agentId = 'default',
        public readonly ?string $sessionId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            server: $data['server'],
            tool: $data['tool'],
            content: $data['content'],
            contentType: $data['contentType'] ?? $data['content_type'] ?? 'text/plain',
            meta: $data['meta'] ?? [],
            agentId: $data['agentId'] ?? $data['agent_id'] ?? 'default',
            sessionId: $data['sessionId'] ?? $data['session_id'] ?? null,
        );
    }

    public function isJson(): bool
    {
        return $this->contentType === 'application/json';
    }

    public function asArray(): array
    {
        if ($this->isJson() && is_string($this->content)) {
            return json_decode($this->content, true) ?? [];
        }

        return is_array($this->content) ? $this->content : [$this->content];
    }

    public function asText(): string
    {
        return is_string($this->content)
            ? $this->content
            : json_encode($this->content);
    }

    public function toArray(): array
    {
        return [
            'server' => $this->server,
            'tool' => $this->tool,
            'content' => $this->content,
            'contentType' => $this->contentType,
            'meta' => $this->meta,
            'agentId' => $this->agentId,
            'sessionId' => $this->sessionId,
        ];
    }
}
