<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Shared\Support;

/**
 * Registry of configured MCP servers
 */
final class McpServerRegistry
{
    /** @var array<string, McpServerConfig> */
    private array $servers = [];

    public function __construct(array $config = [])
    {
        foreach ($config as $name => $serverConfig) {
            $this->register($name, $serverConfig);
        }
    }

    /**
     * Register an MCP server
     */
    public function register(string $name, array|McpServerConfig $config): void
    {
        if (is_array($config)) {
            $config = McpServerConfig::fromArray($name, $config);
        }

        $this->servers[$name] = $config;
    }

    /**
     * Get MCP server configuration
     */
    public function get(string $name): ?McpServerConfig
    {
        return $this->servers[$name] ?? null;
    }

    /**
     * Check if server is registered
     */
    public function has(string $name): bool
    {
        return isset($this->servers[$name]);
    }

    /**
     * Get all registered servers
     */
    public function all(): array
    {
        return $this->servers;
    }

    /**
     * List server names
     */
    public function names(): array
    {
        return array_keys($this->servers);
    }
}
