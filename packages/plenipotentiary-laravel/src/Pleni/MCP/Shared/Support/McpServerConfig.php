<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Shared\Support;

/**
 * Configuration for an MCP server
 */
final class McpServerConfig
{
    public function __construct(
        public readonly string $name,
        public readonly string $transport, // 'stdio' | 'sse'
        public readonly string $command,
        public readonly array $args = [],
        public readonly array $env = [],
        public readonly ?string $url = null, // For SSE transport
    ) {}

    public static function fromArray(string $name, array $config): self
    {
        return new self(
            name: $name,
            transport: $config['transport'] ?? 'stdio',
            command: $config['command'] ?? '',
            args: $config['args'] ?? [],
            env: $config['env'] ?? [],
            url: $config['url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'transport' => $this->transport,
            'command' => $this->command,
            'args' => $this->args,
            'env' => $this->env,
            'url' => $this->url,
        ];
    }
}
