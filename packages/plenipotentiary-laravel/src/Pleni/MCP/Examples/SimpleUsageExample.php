<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Examples;

use Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Actions\CallToolAction;

/**
 * Simple usage example for MCP filesystem operations
 *
 * Run this example:
 * php artisan tinker
 * >>> $example = app(\Plenipotentiary\Laravel\Pleni\MCP\Examples\SimpleUsageExample::class);
 * >>> $example->run();
 */
class SimpleUsageExample
{
    public function __construct(
        private CallToolAction $mcpTool,
    ) {}

    /**
     * Run the simple example
     */
    public function run(): void
    {
        echo "=== MCP Filesystem Example ===\n\n";

        // Example 1: List files in logs directory
        echo "1. Listing files in logs directory...\n";
        $this->listFiles();

        echo "\n";

        // Example 2: Read a file
        echo "2. Reading a log file...\n";
        $this->readFile();

        echo "\n";

        // Example 3: Write a file
        echo "3. Writing a test file...\n";
        $this->writeFile();

        echo "\n";

        // Example 4: Check budget usage
        echo "4. Checking budget usage...\n";
        $this->checkBudget();

        echo "\n=== Example Complete ===\n";
    }

    /**
     * Example 1: List directory contents
     */
    private function listFiles(): void
    {
        $result = $this->mcpTool->handle(
            server: 'filesystem',
            tool: 'list_directory',
            arguments: ['path' => storage_path('logs')],
            agentId: 'simple-example'
        );

        if ($result->isOk()) {
            $files = $result->unwrap()->asArray();
            echo 'Found '.count($files)." files:\n";
            foreach (array_slice($files, 0, 5) as $file) {
                echo "  - {$file}\n";
            }
        } else {
            echo 'Error: '.$result->error()['message']."\n";
        }
    }

    /**
     * Example 2: Read file contents
     */
    private function readFile(): void
    {
        // Try to read Laravel log
        $logPath = storage_path('logs/laravel.log');

        $result = $this->mcpTool->handle(
            server: 'filesystem',
            tool: 'read_file',
            arguments: ['path' => $logPath],
            agentId: 'simple-example'
        );

        if ($result->isOk()) {
            $content = $result->unwrap()->asText();
            $lines = explode("\n", $content);
            $lineCount = count($lines);

            echo "Read {$lineCount} lines from laravel.log\n";
            echo 'First line: '.trim($lines[0])."\n";

            // Show metadata
            $meta = $result->unwrap()->meta;
            echo 'File size: '.number_format($meta['file_size'] ?? 0)." bytes\n";
            echo 'Read time: '.($meta['duration_ms'] ?? 0)." ms\n";
        } else {
            echo 'Error: '.$result->error()['message']."\n";
        }
    }

    /**
     * Example 3: Write a test file
     */
    private function writeFile(): void
    {
        $testPath = storage_path('logs/mcp-test.txt');
        $content = 'MCP test file created at '.now()->toDateTimeString()."\n";
        $content .= "This file was created by the MCP filesystem server.\n";

        $result = $this->mcpTool->handle(
            server: 'filesystem',
            tool: 'write_file',
            arguments: [
                'path' => $testPath,
                'content' => $content,
            ],
            agentId: 'simple-example'
        );

        if ($result->isOk()) {
            $meta = $result->unwrap()->meta;
            echo "Wrote {$meta['bytes_written']} bytes to {$testPath}\n";
        } else {
            echo 'Error: '.$result->error()['message']."\n";
        }
    }

    /**
     * Example 4: Check budget usage
     */
    private function checkBudget(): void
    {
        $tracker = app(\Plenipotentiary\Laravel\Pleni\MCP\Shared\Support\AgentBudgetTracker::class);
        $usage = $tracker->getUsage('simple-example');

        echo "Budget usage for 'simple-example' agent:\n";
        echo "  Calls made: {$usage['call_count']}\n";
        echo '  Total cost: $'.number_format($usage['daily_cost'], 4)."\n";
        echo '  Daily limit: $'.number_format($usage['limit'], 2)."\n";
        echo '  Remaining: $'.number_format($usage['remaining'], 2)."\n";
    }
}
