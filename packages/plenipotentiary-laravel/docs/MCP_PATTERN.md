# MCP (Model Context Protocol) Pattern

## The Problem

AI agents need to interact with local resources and external services through **MCP servers** (tools, resources, prompts). These interactions require:
- **Observability** - Track every tool call the agent makes
- **Safety** - Rate limiting, retries, budget controls
- **Idempotency** - Prevent duplicate actions from agent loops
- **Error Handling** - Normalize MCP server errors to domain exceptions
- **Auditability** - Log all agent decisions and actions

**The challenge:** How do we provide AI agents with the same stability, observability, and safety that our API integrations have?

---

## The Solution: MCP Operation Pattern

MCP servers expose **tools** (callable functions) and **resources** (data access). This maps naturally to Plenipotentiary's Operation pattern.

### Core Principle

> **MCP tools are operations. Use the Operation pattern with agent-specific policies.**

MCP is fundamentally action-based:
- ✅ "Read file from filesystem"
- ✅ "Query database for customer data"
- ✅ "Execute SQL query"
- ✅ "Search code repository"
- ❌ Not CRUD - you don't create/update/delete MCP tools

---

## Pattern Structure

```
Pleni/MCP/
  ├── Contexts/
  │   ├── Default/              ← General-purpose agent operations
  │   │   ├── Operations/
  │   │   │   ├── CallTool/
  │   │   │   │   ├── CallToolOperation.php
  │   │   │   │   ├── CallToolGateway.php
  │   │   │   │   ├── CallToolDTO.php
  │   │   │   │   └── CallToolResult.php
  │   │   │   ├── ReadResource/
  │   │   │   │   ├── ReadResourceOperation.php
  │   │   │   │   ├── ReadResourceGateway.php
  │   │   │   │   ├── ReadResourceDTO.php
  │   │   │   │   └── ReadResourceResult.php
  │   │   │   └── ListTools/
  │   │   │       ├── ListToolsOperation.php
  │   │   │       └── ListToolsResult.php
  │   │   ├── Actions/          ← Laravel Actions (agent business logic)
  │   │   │   ├── CallToolAction.php
  │   │   │   ├── ReadResourceAction.php
  │   │   │   └── AgentWorkflowAction.php
  │   │   ├── Commands/         ← Artisan Commands
  │   │   │   ├── RunAgentCommand.php
  │   │   │   └── ListMcpServersCommand.php
  │   │   ├── Jobs/             ← Queue Jobs
  │   │   │   └── RunAgentWorkflowJob.php
  │   │   └── Providers/
  │   │       └── McpServiceProvider.php
  │   │
  │   └── Specialized/          ← Context-specific agents
  │       └── Operations/
  │           └── FileSystemAgent/
  │
  └── Shared/
      ├── Support/
      │   ├── McpConfig.php
      │   ├── McpErrorMapper.php
      │   ├── McpServerRegistry.php
      │   └── AgentBudgetTracker.php
      ├── Transport/
      │   ├── McpClient.php         ← MCP SDK client wrapper
      │   ├── McpConnector.php      ← SSE/stdio transport
      │   └── McpRequestBuilder.php
      └── Policies/
          ├── AgentBudgetPolicy.php
          ├── AgentRateLimitPolicy.php
          └── AgentAuditPolicy.php
```

---

## Key Components

### 1. Operation (Adapter Layer - MCP-Specific)

The Operation is where **MCP tool call logic** lives.

```php
// src/Pleni/MCP/Contexts/Default/Operations/CallTool/CallToolOperation.php

final class CallToolOperation implements OperationContract
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
        private McpErrorMapper $errorMapper,
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
            if (!$serverConfig) {
                return Result::err([
                    'error' => 'MCP_SERVER_NOT_FOUND',
                    'message' => "MCP server '{$dto->server}' not configured",
                ]);
            }

            // Build MCP tool call request
            $mcpRequest = $this->buildRequest($dto);

            // Execute tool via MCP client
            $mcpResponse = $this->client->callTool($serverConfig, $mcpRequest);

            // Handle MCP errors
            if ($mcpResponse->isError) {
                return $this->handleMcpError($mcpResponse);
            }

            // Map response to result DTO
            $result = $this->mapResponse($mcpResponse, $dto);

            return Result::ok($result);

        } catch (McpConnectionException $e) {
            return Result::err([
                'error' => 'MCP_CONNECTION_FAILED',
                'message' => 'Failed to connect to MCP server',
                'server' => $dto->server,
                'details' => $e->getMessage(),
            ]);
        } catch (McpTimeoutException $e) {
            return Result::err([
                'error' => 'MCP_TIMEOUT',
                'message' => 'MCP tool call timed out',
                'server' => $dto->server,
                'tool' => $dto->tool,
            ]);
        } catch (Throwable $e) {
            return $this->errorMapper->map($e);
        }
    }

    private function buildRequest(CallToolDTO $dto): McpToolCallRequest
    {
        return new McpToolCallRequest(
            name: $dto->tool,
            arguments: $dto->arguments ?? [],
            meta: [
                'agent_id' => $dto->agentId,
                'session_id' => $dto->sessionId,
                'timestamp' => now()->toIso8601String(),
            ]
        );
    }

    private function mapResponse(McpResponse $response, CallToolDTO $request): CallToolResult
    {
        return new CallToolResult(
            server: $request->server,
            tool: $request->tool,
            content: $response->content,
            contentType: $response->contentType ?? 'text/plain',
            meta: array_merge($response->meta ?? [], [
                'duration_ms' => $response->durationMs ?? null,
                'token_count' => $response->tokenCount ?? null,
            ]),
            agentId: $request->agentId,
            sessionId: $request->sessionId,
        );
    }

    private function handleMcpError(McpResponse $response): Result
    {
        return Result::err([
            'error' => 'MCP_TOOL_ERROR',
            'message' => $response->errorMessage ?? 'MCP tool execution failed',
            'code' => $response->errorCode ?? null,
            'details' => $response->content,
        ]);
    }
}
```

### 2. Gateway (Stable Layer - Agent Safety)

The Gateway provides **agent-specific policies** (budget tracking, rate limiting, audit logging).

```php
// src/Pleni/MCP/Contexts/Default/Operations/CallTool/CallToolGateway.php

final class CallToolGateway
{
    public function __construct(
        private CallToolOperation $operation,
        private LoggerInterface $logger,
        private GatewayPolicyChain $policyChain,
        private AgentBudgetTracker $budgetTracker,
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
        ]);

        // Build gateway call context
        $call = new GatewayCall(
            operation: "mcp.{$dto->server}.{$dto->tool}",
            context: array_merge([
                'agent_id' => $dto->agentId,
                'session_id' => $dto->sessionId,
                'server' => $dto->server,
                'tool' => $dto->tool,
            ], $options)
        );

        // Execute through policy chain (budget, rate limit, audit)
        return $this->policyChain->invoke(
            fn() => $this->executeWithBudget($dto),
            $call
        );
    }

    private function executeWithBudget(CallToolDTO $dto): Result
    {
        // Check budget before execution
        if (!$this->budgetTracker->canExecute($dto->agentId)) {
            return Result::err([
                'error' => 'AGENT_BUDGET_EXCEEDED',
                'message' => "Agent '{$dto->agentId}' has exceeded budget",
                'budget' => $this->budgetTracker->getUsage($dto->agentId),
            ]);
        }

        $result = $this->operation->perform($dto);

        // Track usage after execution
        if ($result->isOk()) {
            $this->budgetTracker->recordUsage(
                agentId: $dto->agentId,
                operation: "{$dto->server}.{$dto->tool}",
                cost: $this->calculateCost($result->unwrap())
            );
        }

        return $result;
    }

    private function calculateCost(CallToolResult $result): float
    {
        // Base cost per tool call
        $cost = 0.01;

        // Add token-based cost if available
        if ($tokenCount = $result->meta['token_count'] ?? null) {
            $cost += ($tokenCount / 1000) * 0.002; // $0.002 per 1K tokens
        }

        // Add duration-based cost
        if ($duration = $result->meta['duration_ms'] ?? null) {
            $cost += ($duration / 1000) * 0.001; // $0.001 per second
        }

        return $cost;
    }
}
```

### 3. DTOs (Data Transfer Objects)

**Input DTO** - What the agent/developer provides:

```php
// src/Pleni/MCP/Contexts/Default/Operations/CallTool/CallToolDTO.php

final class CallToolDTO
{
    public function __construct(
        public readonly string $server,
        public readonly string $tool,
        public readonly array $arguments = [],
        public readonly string $agentId,
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
```

**Result DTO** - What gets returned:

```php
// src/Pleni/MCP/Contexts/Default/Operations/CallTool/CallToolResult.php

final class CallToolResult
{
    public function __construct(
        public readonly string $server,
        public readonly string $tool,
        public readonly mixed $content,        // Tool result content
        public readonly string $contentType,   // 'text/plain', 'application/json', etc.
        public readonly array $meta,           // Duration, tokens, etc.
        public readonly string $agentId,
        public readonly ?string $sessionId,
    ) {}

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
```

### 4. Action (Laravel Application Layer - Agent Workflows)

Actions are where **agent business logic** lives.

```php
// src/Pleni/MCP/Contexts/Default/Actions/CallToolAction.php

use Lorisleiva\Actions\Concerns\AsAction;

final class CallToolAction
{
    use AsAction;

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
            sessionId: $sessionId,
        );

        return $this->gateway->call($dto);
    }

    /**
     * As a controller endpoint
     */
    public function asController(Request $request): JsonResponse
    {
        $result = $this->handle(
            server: $request->input('server'),
            tool: $request->input('tool'),
            arguments: $request->input('arguments', []),
            agentId: $request->input('agent_id', 'default'),
            sessionId: $request->input('session_id'),
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
     * As a job (for async agent workflows)
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
            event(new AgentToolCalled($result->unwrap()));
        } else {
            // Handle failure
            Log::error('Agent tool call failed', [
                'server' => $server,
                'tool' => $tool,
                'error' => $result->error(),
            ]);
        }
    }

    /**
     * As a command
     */
    public function asCommand(Command $command): int
    {
        $server = $command->argument('server');
        $tool = $command->argument('tool');
        $argsJson = $command->argument('arguments');

        $arguments = $argsJson ? json_decode($argsJson, true) : [];

        $command->info("Calling MCP tool: {$server}.{$tool}");

        $result = $this->handle($server, $tool, $arguments);

        if ($result->isErr()) {
            $command->error('Tool call failed: ' . $result->error()['message']);
            return Command::FAILURE;
        }

        $toolResult = $result->unwrap();
        $command->info("Result ({$toolResult->contentType}):");
        $command->line($toolResult->asText());

        if ($meta = $toolResult->meta) {
            $command->newLine();
            $command->info('Meta:');
            $command->table(
                ['Key', 'Value'],
                collect($meta)->map(fn($v, $k) => [$k, $v])->toArray()
            );
        }

        return Command::SUCCESS;
    }
}
```

### 5. Agent Workflow Example

```php
// src/Pleni/MCP/Contexts/Default/Actions/AgentWorkflowAction.php

final class AgentWorkflowAction
{
    use AsAction;

    public function __construct(
        private CallToolAction $callTool,
    ) {}

    /**
     * Example: Campaign optimizer agent workflow
     */
    public function optimizeCampaign(int $campaignId, string $agentId = 'campaign-optimizer'): Result
    {
        $sessionId = Str::uuid()->toString();

        // 1. Read campaign performance data via MCP
        $perfResult = $this->callTool->handle(
            server: 'analytics',
            tool: 'query_campaign_performance',
            arguments: [
                'campaign_id' => $campaignId,
                'days' => 30,
                'metrics' => ['click_through_rate', 'cost_per_click', 'conversions'],
            ],
            agentId: $agentId,
            sessionId: $sessionId,
        );

        if ($perfResult->isErr()) {
            return $perfResult; // Propagate error
        }

        $performanceData = $perfResult->unwrap()->asArray();

        // 2. Use LLM to analyze performance via MCP
        $analysisResult = $this->callTool->handle(
            server: 'openai',
            tool: 'analyze_campaign',
            arguments: [
                'data' => $performanceData,
                'prompt' => 'Analyze this campaign performance and suggest bid adjustments to improve ROI.',
                'model' => 'gpt-4',
            ],
            agentId: $agentId,
            sessionId: $sessionId,
        );

        if ($analysisResult->isErr()) {
            return $analysisResult;
        }

        $suggestions = json_decode($analysisResult->unwrap()->asText(), true);

        // 3. Execute changes via existing Google Ads gateway (not MCP)
        // ... (this would use the existing CampaignCrudGateway)

        return Result::ok(new AgentWorkflowResult(
            agentId: $agentId,
            sessionId: $sessionId,
            steps: [
                'performance_query' => $performanceData,
                'llm_analysis' => $suggestions,
                // 'campaign_update' => $updateResult,
            ],
        ));
    }
}
```

---

## Agent-Specific Policies

### 1. Agent Budget Policy

```php
// src/Pleni/MCP/Shared/Policies/AgentBudgetPolicy.php

final class AgentBudgetPolicy implements GatewayPolicy
{
    public function __construct(
        private AgentBudgetTracker $tracker,
    ) {}

    public function before(GatewayCall $call): GatewayCall
    {
        $agentId = $call->context['agent_id'] ?? 'default';

        if (!$this->tracker->canExecute($agentId)) {
            throw new AgentBudgetExceededException(
                "Agent '{$agentId}' has exceeded budget limit"
            );
        }

        return $call;
    }

    public function after(GatewayCall $call, Result $result): Result
    {
        // Usage tracking happens in gateway
        return $result;
    }

    public function onError(GatewayCall $call, Throwable|Result $error): Result
    {
        // Don't charge for errors
        return $error instanceof Result ? $error : Result::err($error);
    }
}
```

### 2. Agent Rate Limit Policy

```php
// src/Pleni/MCP/Shared/Policies/AgentRateLimitPolicy.php

final class AgentRateLimitPolicy implements GatewayPolicy
{
    public function __construct(
        private RateLimiter $limiter,
    ) {}

    public function before(GatewayCall $call): GatewayCall
    {
        $agentId = $call->context['agent_id'] ?? 'default';
        $key = "agent:{$agentId}:rate_limit";

        // Limit: 100 tool calls per minute per agent
        if (!$this->limiter->attempt($key, 100, 60)) {
            throw new AgentRateLimitException(
                "Agent '{$agentId}' exceeded rate limit (100 calls/min)"
            );
        }

        return $call;
    }

    public function after(GatewayCall $call, Result $result): Result
    {
        return $result;
    }

    public function onError(GatewayCall $call, Throwable|Result $error): Result
    {
        return $error instanceof Result ? $error : Result::err($error);
    }
}
```

### 3. Agent Audit Policy

```php
// src/Pleni/MCP/Shared/Policies/AgentAuditPolicy.php

final class AgentAuditPolicy implements GatewayPolicy
{
    public function __construct(
        private AgentAuditLog $auditLog,
    ) {}

    public function before(GatewayCall $call): GatewayCall
    {
        $this->auditLog->logToolCall([
            'agent_id' => $call->context['agent_id'] ?? 'default',
            'session_id' => $call->context['session_id'] ?? null,
            'operation' => $call->operation,
            'server' => $call->context['server'] ?? null,
            'tool' => $call->context['tool'] ?? null,
            'timestamp' => now(),
            'status' => 'started',
        ]);

        return $call;
    }

    public function after(GatewayCall $call, Result $result): Result
    {
        $this->auditLog->logToolCall([
            'agent_id' => $call->context['agent_id'] ?? 'default',
            'session_id' => $call->context['session_id'] ?? null,
            'operation' => $call->operation,
            'timestamp' => now(),
            'status' => $result->isOk() ? 'success' : 'failed',
            'result' => $result->toArray(),
        ]);

        return $result;
    }

    public function onError(GatewayCall $call, Throwable|Result $error): Result
    {
        $this->auditLog->logToolCall([
            'agent_id' => $call->context['agent_id'] ?? 'default',
            'session_id' => $call->context['session_id'] ?? null,
            'operation' => $call->operation,
            'timestamp' => now(),
            'status' => 'error',
            'error' => $error instanceof Result
                ? $error->error()
                : ['exception' => $error::class, 'message' => $error->getMessage()],
        ]);

        return $error instanceof Result ? $error : Result::err($error);
    }
}
```

---

## Real-World Use Cases

### Use Case 1: File System Agent

**Developer writes:**

```php
// Agent reads file via MCP filesystem server
$result = $callToolAction->handle(
    server: 'filesystem',
    tool: 'read_file',
    arguments: ['path' => '/var/www/logs/error.log'],
    agentId: 'log-analyzer',
);

if ($result->isOk()) {
    $content = $result->unwrap()->asText();
    // Process log content...
}
```

### Use Case 2: Database Query Agent

**Developer writes:**

```php
// Agent queries database via MCP
$result = $callToolAction->handle(
    server: 'database',
    tool: 'execute_query',
    arguments: [
        'query' => 'SELECT * FROM customers WHERE status = ?',
        'bindings' => ['active'],
    ],
    agentId: 'customer-analyzer',
);

if ($result->isOk()) {
    $customers = $result->unwrap()->asArray();
    // Analyze customers...
}
```

### Use Case 3: Multi-Step Agent Workflow

**Developer writes:**

```php
// Complex agent workflow
class ResearchAgentWorkflow
{
    public function research(string $topic): Result
    {
        $sessionId = Str::uuid();
        $agentId = 'research-agent';

        // 1. Search codebase
        $searchResult = $this->callTool->handle(
            server: 'code-search',
            tool: 'search_files',
            arguments: ['query' => $topic],
            agentId: $agentId,
            sessionId: $sessionId,
        );

        // 2. Read relevant files
        foreach ($searchResult->unwrap()->asArray()['files'] as $file) {
            $fileResult = $this->callTool->handle(
                server: 'filesystem',
                tool: 'read_file',
                arguments: ['path' => $file['path']],
                agentId: $agentId,
                sessionId: $sessionId,
            );

            // 3. Analyze with LLM
            $analysisResult = $this->callTool->handle(
                server: 'openai',
                tool: 'analyze_code',
                arguments: [
                    'code' => $fileResult->unwrap()->asText(),
                    'prompt' => "Analyze this code for: {$topic}",
                ],
                agentId: $agentId,
                sessionId: $sessionId,
            );

            // Store findings...
        }

        return Result::ok($findings);
    }
}
```

---

## MCP Server Configuration

```php
// config/mcp.php

return [
    'servers' => [
        'filesystem' => [
            'transport' => 'stdio',
            'command' => 'npx',
            'args' => ['-y', '@modelcontextprotocol/server-filesystem', '/var/www'],
            'env' => [],
        ],

        'database' => [
            'transport' => 'stdio',
            'command' => 'npx',
            'args' => ['-y', '@modelcontextprotocol/server-sqlite'],
            'env' => [
                'DB_PATH' => storage_path('database.sqlite'),
            ],
        ],

        'code-search' => [
            'transport' => 'sse',
            'url' => 'http://localhost:3000/sse',
            'auth' => [
                'type' => 'bearer',
                'token' => env('CODE_SEARCH_TOKEN'),
            ],
        ],

        'openai' => [
            'transport' => 'stdio',
            'command' => 'npx',
            'args' => ['-y', '@modelcontextprotocol/server-openai'],
            'env' => [
                'OPENAI_API_KEY' => env('OPENAI_API_KEY'),
            ],
        ],
    ],

    'agent_budgets' => [
        'default' => [
            'daily_limit' => 10.00,  // $10 per day
            'per_call_limit' => 1.00, // $1 per call
        ],
        'campaign-optimizer' => [
            'daily_limit' => 50.00,
            'per_call_limit' => 5.00,
        ],
    ],

    'rate_limits' => [
        'default' => [
            'calls_per_minute' => 100,
            'calls_per_hour' => 1000,
        ],
    ],
];
```

---

## Benefits for AI Agents

### 1. Observability

```php
// Every tool call is logged
Log::info('gateway.mcp.call', [
    'agent_id' => 'campaign-optimizer',
    'server' => 'filesystem',
    'tool' => 'read_file',
    'duration_ms' => 150,
    'cost' => 0.015,
]);
```

### 2. Safety Guards

```php
// Budget exceeded - agent stops automatically
AgentBudgetExceededException: Agent 'runaway-agent' exceeded $10 daily limit

// Rate limit prevents loops
AgentRateLimitException: Agent exceeded 100 calls/min

// Idempotency prevents duplicates (via gateway)
```

### 3. Error Recovery

```php
// Agent doesn't crash on MCP failures
$result = $callToolAction->handle('database', 'query', [...]);

if ($result->isErr()) {
    // Gracefully handle
    Log::warning('Database query failed, using cached data');
    return $this->fallbackToCachedData();
}
```

### 4. Auditability

```php
// Full audit trail for compliance
AgentAuditLog::where('agent_id', 'financial-agent')
    ->where('session_id', $sessionId)
    ->orderBy('created_at')
    ->get();
// Shows complete decision trail
```

---

## When to Use MCP Pattern

### Use MCP When:
- ✅ Building AI agents that need local resource access
- ✅ Agents need to query databases, read files, execute commands
- ✅ Need observability into agent actions
- ✅ Need budget/rate limit controls
- ✅ Need audit trail for compliance
- ✅ Want idempotency for agent loops

### Don't Use MCP When:
- ❌ Direct HTTP API integration is simpler (use REST/CRUD)
- ❌ No need for agent autonomy
- ❌ Simple one-time scripts (use plain Laravel)

---

## Combining MCP with Other Patterns

You can mix MCP with existing patterns!

```php
class CampaignOptimizerAgent
{
    public function __construct(
        private CallToolAction $mcpTool,           // MCP for local data
        private GoogleAdsCrudGateway $adsGateway,  // CRUD for campaigns
        private StripeGateway $billingGateway,     // REST for billing
    ) {}

    public function optimize(int $campaignId): void
    {
        // 1. Read analytics via MCP
        $perf = $this->mcpTool->handle('analytics', 'query', [...]);

        // 2. Analyze with LLM via MCP
        $analysis = $this->mcpTool->handle('openai', 'analyze', [...]);

        // 3. Update campaign via existing CRUD gateway
        $this->adsGateway->update($campaignDto);

        // 4. Bill customer via existing REST gateway
        $this->billingGateway->createInvoice($invoiceDto);
    }
}
```

**All patterns work together seamlessly!**

---

## Summary

### Key Principles

1. ✅ **MCP tools are operations** - Use Operation pattern, not CRUD
2. ✅ **Use DTOs** - Type-safe CallToolDTO and CallToolResult
3. ✅ **Gateway for agent safety** - Budget, rate limits, audit logging
4. ✅ **Operation for MCP logic** - Tool calls, error handling
5. ✅ **Action for workflows** - Multi-step agent business logic
6. ✅ **Policies for control** - Budget, rate limit, audit policies

### The Developer Experience

**Developer wants:** "Agent reads file and analyzes with LLM"

**Developer writes:**
```php
$fileResult = $callToolAction->handle('filesystem', 'read_file', ['path' => $path]);
$analysis = $callToolAction->handle('openai', 'analyze', ['content' => $fileResult->unwrap()->asText()]);
```

**Not:**
```php
// Manual MCP protocol handling, no safety, no observability
$mcpClient->send(['jsonrpc' => '2.0', 'method' => 'tools/call', ...]);
```

The MCP Operation pattern gives AI agents the same stability, observability, and safety guarantees that API integrations have!
