# MCP Quick Reference

---

## Installation

```bash
# Optional: Install actual MCP server (works without it)
npm install -g @modelcontextprotocol/server-filesystem
```

---

## Configuration

```php
// config/mcp.php
return [
    'servers' => [
        'filesystem' => [
            'transport' => 'stdio',
            'command' => 'npx',
            'args' => ['-y', '@modelcontextprotocol/server-filesystem', storage_path('logs')],
        ],
    ],
    'agent_budgets' => [
        'default' => ['daily_limit' => 10.00],
    ],
];
```

---

## Basic Usage

```php
use Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Actions\CallToolAction;

$action = app(CallToolAction::class);

$result = $action->handle(
    server: 'filesystem',
    tool: 'read_file',
    arguments: ['path' => storage_path('logs/laravel.log')],
    agentId: 'my-agent'
);

if ($result->isOk()) {
    echo $result->unwrap()->asText();
}
```

---

## Available Tools

### Read File
```php
$result = $action->handle('filesystem', 'read_file', [
    'path' => '/path/to/file'
]);
```

### List Directory
```php
$result = $action->handle('filesystem', 'list_directory', [
    'path' => '/path/to/dir'
]);
```

### Write File
```php
$result = $action->handle('filesystem', 'write_file', [
    'path' => '/path/to/file',
    'content' => 'Hello World'
]);
```

---

## Laravel Integration

### Controller
```php
Route::post('/api/mcp/call', function (Request $request, CallToolAction $action) {
    return $action->asController($request);
});
```

### Command
```php
class McpCallCommand extends Command {
    public function handle(CallToolAction $action): int {
        return $action->asCommand($this);
    }
}

// php artisan mcp:call filesystem read_file '{"path":"..."}' --agent=admin
```

### Job
```php
class McpJob implements ShouldQueue {
    public function handle(CallToolAction $action): void {
        $action->asJob('filesystem', 'read_file', [...], 'job-agent');
    }
}
```

---

## Error Handling

```php
$result = $action->handle(...);

if ($result->isErr()) {
    $error = $result->error();
    // ['error' => 'MCP_TOOL_ERROR', 'message' => '...', 'code' => '...']
}

if ($result->isOk()) {
    $data = $result->unwrap(); // CallToolResult
    echo $data->asText();      // or $data->asArray()
}
```

---

## Budget Tracking

```php
use Plenipotentiary\Laravel\Pleni\MCP\Shared\Support\AgentBudgetTracker;

$tracker = app(AgentBudgetTracker::class);

// Check usage
$usage = $tracker->getUsage('my-agent');
// ['daily_cost' => 0.15, 'call_count' => 12, 'limit' => 10.00, ...]

// Reset budget
$tracker->reset('my-agent');
```

---

## Examples

### Simple Example
```bash
php artisan tinker
>>> $ex = app(\Plenipotentiary\Laravel\Pleni\MCP\Examples\SimpleUsageExample::class);
>>> $ex->run();
```

### Log Analyzer Agent
```php
use Plenipotentiary\Laravel\Pleni\MCP\Examples\LogAnalyzerAgent;

$agent = new LogAnalyzerAgent(app(CallToolAction::class));
$result = $agent->analyzeLogs(storage_path('logs'));
```

---

## Common Errors

| Error | Meaning | Fix |
|-------|---------|-----|
| `MCP_SERVER_NOT_FOUND` | Server not in config | Add to `config/mcp.php` |
| `FILE_NOT_FOUND` | File doesn't exist | Check path |
| `ACCESS_DENIED` | Path outside allowed roots | Update config args |
| `AGENT_BUDGET_EXCEEDED` | Daily limit reached | Increase limit or reset |

---

## File Structure

```
CallToolAction        → Use this (Laravel layer)
    ↓
CallToolGateway       → Budget/policies
    ↓
CallToolOperation     → MCP tool calls
    ↓
McpClient             → Communicates with servers
```

---

## Key Concepts

- **Agent ID**: Identifies who's calling (for budget tracking)
- **Session ID**: Groups related calls (for workflow tracking)
- **Server**: MCP server name (`filesystem`, `database`, etc.)
- **Tool**: Operation name (`read_file`, `list_directory`, etc.)
- **Arguments**: Tool-specific parameters

---

## Documentation

- **Pattern Guide**: `docs/MCP_PATTERN.md`
- **Usage Examples**: `src/Pleni/MCP/Examples/USAGE_EXAMPLE.md`
- **Implementation**: `src/Pleni/MCP/IMPLEMENTATION_SUMMARY.md`
- **README**: `src/Pleni/MCP/README.md`

---

## Quick Test

```php
// Read this file
$result = app(\Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Actions\CallToolAction::class)
    ->handle('filesystem', 'read_file', ['path' => __FILE__], 'test');

if ($result->isOk()) {
    echo "Success! Read " . strlen($result->unwrap()->asText()) . " bytes\n";
}
```
