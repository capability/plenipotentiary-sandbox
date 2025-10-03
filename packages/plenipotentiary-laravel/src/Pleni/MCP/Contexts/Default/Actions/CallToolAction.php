<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Actions;

use Illuminate\Console\Command;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Operations\CallTool\CallToolDTO;
use Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Operations\CallTool\CallToolGateway;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Laravel Action for MCP tool calls
 *
 * Can be used as:
 * - Controller method
 * - Job
 * - Console command
 * - Direct invocation
 */
final class CallToolAction
{
    public function __construct(
        private CallToolGateway $gateway,
    ) {}

    /**
     * Handle MCP tool call
     */
    public function handle(
        string $server,
        string $tool,
        array $arguments = [],
        string $agentId = 'default',
        ?string $sessionId = null
    ): Result {
        $dto = new CallToolDTO(
            server: $server,
            tool: $tool,
            arguments: $arguments,
            agentId: $agentId,
            sessionId: $sessionId ?? \Str::uuid()->toString(),
        );

        return $this->gateway->call($dto);
    }

    /**
     * Use as a controller endpoint
     *
     * POST /api/mcp/call
     * {
     *   "server": "filesystem",
     *   "tool": "read_file",
     *   "arguments": {"path": "/path/to/file"},
     *   "agent_id": "my-agent"
     * }
     */
    public function asController(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server' => 'required|string',
            'tool' => 'required|string',
            'arguments' => 'sometimes|array',
            'agent_id' => 'sometimes|string',
            'session_id' => 'sometimes|string',
        ]);

        $result = $this->handle(
            server: $validated['server'],
            tool: $validated['tool'],
            arguments: $validated['arguments'] ?? [],
            agentId: $validated['agent_id'] ?? 'default',
            sessionId: $validated['session_id'] ?? null,
        );

        if ($result->isOk()) {
            return response()->json([
                'success' => true,
                'data' => $result->unwrap()->toArray(),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result->error(),
        ], 400);
    }

    /**
     * Use as a job (for async agent workflows)
     *
     * dispatch(new CallToolJob('filesystem', 'read_file', [...]));
     */
    public function asJob(
        string $server,
        string $tool,
        array $arguments = [],
        string $agentId = 'default'
    ): void {
        $result = $this->handle($server, $tool, $arguments, $agentId);

        if ($result->isOk()) {
            // Store result, trigger events, etc.
            \Log::info('Agent tool call completed', [
                'server' => $server,
                'tool' => $tool,
                'agent_id' => $agentId,
                'result' => $result->unwrap()->toArray(),
            ]);
        } else {
            // Handle failure
            \Log::error('Agent tool call failed', [
                'server' => $server,
                'tool' => $tool,
                'agent_id' => $agentId,
                'error' => $result->error(),
            ]);
        }
    }

    /**
     * Use as a command
     *
     * php artisan mcp:call filesystem read_file '{"path":"/tmp/test.txt"}' --agent=log-analyzer
     */
    public function asCommand(Command $command): int
    {
        $server = $command->argument('server');
        $tool = $command->argument('tool');
        $argsJson = $command->argument('arguments') ?? '{}';
        $agentId = $command->option('agent') ?? 'cli';

        try {
            $arguments = json_decode($argsJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $command->error('Invalid JSON arguments: '.$e->getMessage());

            return Command::FAILURE;
        }

        $command->info("Calling MCP tool: {$server}.{$tool}");
        $command->line("Agent: {$agentId}");

        $result = $this->handle($server, $tool, $arguments, $agentId);

        if ($result->isErr()) {
            $error = $result->error();
            $command->error('Tool call failed: '.($error['message'] ?? 'Unknown error'));
            $command->line('Error details:');
            $command->line(json_encode($error, JSON_PRETTY_PRINT));

            return Command::FAILURE;
        }

        $toolResult = $result->unwrap();
        $command->info("Result ({$toolResult->contentType}):");
        $command->newLine();

        // Display content based on type
        if ($toolResult->isJson()) {
            $command->line(json_encode($toolResult->asArray(), JSON_PRETTY_PRINT));
        } else {
            $command->line($toolResult->asText());
        }

        // Display metadata
        if ($meta = $toolResult->meta) {
            $command->newLine();
            $command->info('Metadata:');
            $command->table(
                ['Key', 'Value'],
                collect($meta)->map(fn ($v, $k) => [$k, is_array($v) ? json_encode($v) : $v])->toArray()
            );
        }

        return Command::SUCCESS;
    }
}
