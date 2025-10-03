<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Operations\CallTool;

use Plenipotentiary\Laravel\Pleni\MCP\Shared\Support\McpServerRegistry;
use Plenipotentiary\Laravel\Pleni\MCP\Shared\Transport\McpClient;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

/**
 * Operation for calling MCP tools
 *
 * This follows the Operation pattern (not CRUD) because MCP tools
 * are actions/queries, not resources with lifecycle.
 */
final class CallToolOperation
{
    public const INPUT_SPEC = [
        'server' => [
            'rules' => ['required', 'string'],
            'description' => 'MCP server identifier (e.g., "filesystem", "database")',
        ],
        'tool' => [
            'rules' => ['required', 'string'],
            'description' => 'Tool name (e.g., "read_file", "execute_query")',
        ],
        'arguments' => [
            'rules' => ['array'],
            'description' => 'Tool-specific arguments',
        ],
        'agentId' => [
            'rules' => ['required', 'string'],
            'description' => 'Unique identifier for the agent making this call',
        ],
        'sessionId' => [
            'rules' => ['nullable', 'string'],
            'description' => 'Conversation session ID for tracking',
        ],
    ];

    public function __construct(
        private McpClient $client,
        private McpServerRegistry $registry,
        private LoggerInterface $logger,
    ) {}

    public static function inputSpec(): array
    {
        return self::INPUT_SPEC;
    }

    /**
     * Execute an MCP tool call
     */
    public function perform(CallToolDTO $dto): Result
    {
        try {
            $this->logger->info('MCP: Calling tool', [
                'server' => $dto->server,
                'tool' => $dto->tool,
                'agent_id' => $dto->agentId,
                'session_id' => $dto->sessionId,
            ]);

            // Get MCP server configuration
            $serverConfig = $this->registry->get($dto->server);
            if (! $serverConfig) {
                return Result::err([
                    'error' => 'MCP_SERVER_NOT_FOUND',
                    'message' => "MCP server '{$dto->server}' not configured",
                    'available_servers' => $this->registry->names(),
                ]);
            }

            // Execute tool via MCP client
            $mcpResponse = $this->client->callTool($serverConfig, $dto->tool, $dto->arguments);

            // Handle MCP errors
            if ($mcpResponse->isError) {
                return Result::err([
                    'error' => 'MCP_TOOL_ERROR',
                    'message' => $mcpResponse->errorMessage ?? 'MCP tool execution failed',
                    'code' => $mcpResponse->errorCode,
                    'server' => $dto->server,
                    'tool' => $dto->tool,
                ]);
            }

            // Map response to result DTO
            $result = new CallToolResult(
                server: $dto->server,
                tool: $dto->tool,
                content: $mcpResponse->content,
                contentType: $mcpResponse->contentType,
                meta: $mcpResponse->meta,
                agentId: $dto->agentId,
                sessionId: $dto->sessionId,
            );

            $this->logger->info('MCP: Tool call successful', [
                'server' => $dto->server,
                'tool' => $dto->tool,
                'content_type' => $result->contentType,
                'meta' => $result->meta,
            ]);

            return Result::ok($result);

        } catch (\Exception $e) {
            $this->logger->error('MCP: Tool call exception', [
                'server' => $dto->server,
                'tool' => $dto->tool,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Result::err([
                'error' => 'MCP_EXCEPTION',
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        }
    }
}
