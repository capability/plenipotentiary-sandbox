<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Shared\Transport;

use Plenipotentiary\Laravel\Pleni\MCP\Shared\Support\McpResponse;
use Plenipotentiary\Laravel\Pleni\MCP\Shared\Support\McpServerConfig;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Simple MCP client for stdio transport
 *
 * This is a minimal implementation for demonstration.
 * In production, you'd use a proper MCP SDK or implement the full JSON-RPC protocol.
 */
final class McpClient
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    /**
     * Call an MCP tool via stdio transport
     */
    public function callTool(McpServerConfig $config, string $tool, array $arguments): McpResponse
    {
        $this->logger->info('MCP: Calling tool via stdio', [
            'server' => $config->name,
            'tool' => $tool,
            'command' => $config->command,
        ]);

        try {
            // For filesystem MCP server, we'll simulate the protocol
            // In production, this would use proper JSON-RPC 2.0 over stdio

            if ($config->name === 'filesystem') {
                return $this->callFilesystemTool($config, $tool, $arguments);
            }

            // Generic MCP tool call via stdio (simplified)
            return $this->executeStdioToolCall($config, $tool, $arguments);

        } catch (\Exception $e) {
            $this->logger->error('MCP: Tool call failed', [
                'server' => $config->name,
                'tool' => $tool,
                'error' => $e->getMessage(),
            ]);

            return McpResponse::error(
                message: $e->getMessage(),
                code: 'MCP_EXECUTION_ERROR'
            );
        }
    }

    /**
     * Simplified filesystem tool implementation
     *
     * In production, this would communicate with @modelcontextprotocol/server-filesystem
     * via proper JSON-RPC protocol over stdio
     */
    private function callFilesystemTool(McpServerConfig $config, string $tool, array $arguments): McpResponse
    {
        $startTime = microtime(true);

        switch ($tool) {
            case 'read_file':
                $path = $arguments['path'] ?? null;
                if (! $path) {
                    return McpResponse::error('Missing required argument: path', 'INVALID_PARAMS');
                }

                // Security: ensure path is absolute and within allowed roots
                $allowedRoots = $config->args; // MCP filesystem server args are allowed directories
                if (! $this->isPathAllowed($path, $allowedRoots)) {
                    return McpResponse::error(
                        "Path '{$path}' is not within allowed directories",
                        'ACCESS_DENIED'
                    );
                }

                if (! file_exists($path)) {
                    return McpResponse::error("File not found: {$path}", 'FILE_NOT_FOUND');
                }

                if (! is_file($path)) {
                    return McpResponse::error("Path is not a file: {$path}", 'NOT_A_FILE');
                }

                $content = file_get_contents($path);
                $duration = (microtime(true) - $startTime) * 1000;

                return McpResponse::success(
                    content: $content,
                    contentType: 'text/plain',
                    meta: [
                        'duration_ms' => round($duration, 2),
                        'file_size' => strlen($content),
                        'path' => $path,
                    ]
                );

            case 'list_directory':
                $path = $arguments['path'] ?? null;
                if (! $path) {
                    return McpResponse::error('Missing required argument: path', 'INVALID_PARAMS');
                }

                if (! $this->isPathAllowed($path, $config->args)) {
                    return McpResponse::error(
                        "Path '{$path}' is not within allowed directories",
                        'ACCESS_DENIED'
                    );
                }

                if (! is_dir($path)) {
                    return McpResponse::error("Not a directory: {$path}", 'NOT_A_DIRECTORY');
                }

                $files = array_diff(scandir($path), ['.', '..']);
                $duration = (microtime(true) - $startTime) * 1000;

                return McpResponse::success(
                    content: json_encode(array_values($files)),
                    contentType: 'application/json',
                    meta: [
                        'duration_ms' => round($duration, 2),
                        'count' => count($files),
                        'path' => $path,
                    ]
                );

            case 'write_file':
                $path = $arguments['path'] ?? null;
                $content = $arguments['content'] ?? null;

                if (! $path || $content === null) {
                    return McpResponse::error('Missing required arguments: path, content', 'INVALID_PARAMS');
                }

                if (! $this->isPathAllowed($path, $config->args)) {
                    return McpResponse::error(
                        "Path '{$path}' is not within allowed directories",
                        'ACCESS_DENIED'
                    );
                }

                $bytesWritten = file_put_contents($path, $content);
                $duration = (microtime(true) - $startTime) * 1000;

                return McpResponse::success(
                    content: json_encode(['bytes_written' => $bytesWritten]),
                    contentType: 'application/json',
                    meta: [
                        'duration_ms' => round($duration, 2),
                        'bytes_written' => $bytesWritten,
                        'path' => $path,
                    ]
                );

            default:
                return McpResponse::error("Unknown tool: {$tool}", 'METHOD_NOT_FOUND');
        }
    }

    /**
     * Generic stdio tool call (for other MCP servers)
     *
     * This would implement full JSON-RPC 2.0 protocol in production
     */
    private function executeStdioToolCall(McpServerConfig $config, string $tool, array $arguments): McpResponse
    {
        // Build JSON-RPC request
        $request = [
            'jsonrpc' => '2.0',
            'id' => uniqid(),
            'method' => 'tools/call',
            'params' => [
                'name' => $tool,
                'arguments' => $arguments,
            ],
        ];

        // Build process command
        $process = new Process(
            command: array_merge([$config->command], $config->args),
            env: array_merge(getenv(), $config->env),
            timeout: 30,
        );

        // Send request to stdin
        $process->setInput(json_encode($request)."\n");

        try {
            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            $response = json_decode($output, true);

            if (isset($response['error'])) {
                return McpResponse::error(
                    message: $response['error']['message'] ?? 'Unknown error',
                    code: (string) ($response['error']['code'] ?? 'UNKNOWN'),
                );
            }

            return McpResponse::success(
                content: $response['result']['content'] ?? $output,
                meta: [
                    'duration_ms' => $process->getTimeout() ?? 0,
                ]
            );

        } catch (ProcessFailedException $e) {
            return McpResponse::error(
                message: 'Process failed: '.$e->getMessage(),
                code: 'PROCESS_FAILED'
            );
        }
    }

    /**
     * Check if a path is within allowed directories
     */
    private function isPathAllowed(string $path, array $allowedRoots): bool
    {
        $realPath = realpath($path) ?: $path;

        foreach ($allowedRoots as $root) {
            $realRoot = realpath($root) ?: $root;
            if (str_starts_with($realPath, $realRoot)) {
                return true;
            }
        }

        return false;
    }

    /**
     * List available tools from MCP server
     */
    public function listTools(McpServerConfig $config): array
    {
        // Simplified - would use JSON-RPC tools/list method in production
        if ($config->name === 'filesystem') {
            return [
                ['name' => 'read_file', 'description' => 'Read file contents'],
                ['name' => 'list_directory', 'description' => 'List directory contents'],
                ['name' => 'write_file', 'description' => 'Write content to file'],
            ];
        }

        return [];
    }
}
