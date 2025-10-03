<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Examples;

use Illuminate\Support\Str;
use Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Actions\CallToolAction;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Example: Log Analyzer Agent
 *
 * This agent demonstrates a multi-step workflow using MCP:
 * 1. List log files in a directory
 * 2. Read each log file
 * 3. Analyze errors
 * 4. Generate summary report
 *
 * This showcases the MCP pattern in action with real filesystem operations.
 */
final class LogAnalyzerAgent
{
    public function __construct(
        private CallToolAction $mcpTool,
        private string $agentId = 'log-analyzer',
    ) {}

    /**
     * Analyze logs in a directory and generate error summary
     */
    public function analyzeLogs(string $logDirectory): Result
    {
        $sessionId = Str::uuid()->toString();
        $errors = [];
        $warnings = [];
        $filesAnalyzed = 0;

        // Step 1: List log files in directory
        $listResult = $this->mcpTool->handle(
            server: 'filesystem',
            tool: 'list_directory',
            arguments: ['path' => $logDirectory],
            agentId: $this->agentId,
            sessionId: $sessionId,
        );

        if ($listResult->isErr()) {
            return $listResult; // Propagate error
        }

        $files = $listResult->unwrap()->asArray();
        $logFiles = array_filter($files, fn ($f) => str_ends_with($f, '.log'));

        // Step 2: Read and analyze each log file
        foreach ($logFiles as $logFile) {
            $filePath = rtrim($logDirectory, '/').'/'.$logFile;

            $readResult = $this->mcpTool->handle(
                server: 'filesystem',
                tool: 'read_file',
                arguments: ['path' => $filePath],
                agentId: $this->agentId,
                sessionId: $sessionId,
            );

            if ($readResult->isErr()) {
                \Log::warning("Failed to read log file: {$filePath}", [
                    'error' => $readResult->error(),
                ]);

                continue;
            }

            $content = $readResult->unwrap()->asText();
            $filesAnalyzed++;

            // Step 3: Analyze log content
            $analysis = $this->analyzeLogContent($content, $logFile);
            $errors = array_merge($errors, $analysis['errors']);
            $warnings = array_merge($warnings, $analysis['warnings']);
        }

        // Step 4: Generate summary report
        $report = $this->generateReport([
            'session_id' => $sessionId,
            'directory' => $logDirectory,
            'files_analyzed' => $filesAnalyzed,
            'total_errors' => count($errors),
            'total_warnings' => count($warnings),
            'errors' => array_slice($errors, 0, 10), // Top 10 errors
            'warnings' => array_slice($warnings, 0, 10), // Top 10 warnings
        ]);

        // Step 5: Write report to file
        $reportPath = $logDirectory.'/analysis-report.json';
        $writeResult = $this->mcpTool->handle(
            server: 'filesystem',
            tool: 'write_file',
            arguments: [
                'path' => $reportPath,
                'content' => json_encode($report, JSON_PRETTY_PRINT),
            ],
            agentId: $this->agentId,
            sessionId: $sessionId,
        );

        if ($writeResult->isErr()) {
            \Log::error('Failed to write report', [
                'path' => $reportPath,
                'error' => $writeResult->error(),
            ]);
        }

        return Result::ok(
            \Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Operations\CallTool\CallToolResult::fromArray([
                'server' => 'log-analyzer',
                'tool' => 'analyze_logs',
                'content' => $report,
                'contentType' => 'application/json',
                'agentId' => $this->agentId,
                'sessionId' => $sessionId,
                'meta' => [
                    'files_analyzed' => $filesAnalyzed,
                    'report_path' => $reportPath,
                ],
            ])
        );
    }

    /**
     * Analyze log file content for errors and warnings
     */
    private function analyzeLogContent(string $content, string $filename): array
    {
        $lines = explode("\n", $content);
        $errors = [];
        $warnings = [];

        foreach ($lines as $lineNum => $line) {
            // Look for error patterns
            if (preg_match('/\b(ERROR|CRITICAL|FATAL)\b/i', $line)) {
                $errors[] = [
                    'file' => $filename,
                    'line' => $lineNum + 1,
                    'message' => trim($line),
                    'severity' => $this->extractSeverity($line),
                ];
            }

            // Look for warning patterns
            if (preg_match('/\b(WARNING|WARN)\b/i', $line)) {
                $warnings[] = [
                    'file' => $filename,
                    'line' => $lineNum + 1,
                    'message' => trim($line),
                ];
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Extract severity from log line
     */
    private function extractSeverity(string $line): string
    {
        if (preg_match('/\b(CRITICAL|FATAL)\b/i', $line)) {
            return 'CRITICAL';
        }
        if (preg_match('/\bERROR\b/i', $line)) {
            return 'ERROR';
        }

        return 'UNKNOWN';
    }

    /**
     * Generate analysis report
     */
    private function generateReport(array $data): array
    {
        return [
            'agent' => $this->agentId,
            'session_id' => $data['session_id'],
            'analyzed_at' => now()->toIso8601String(),
            'summary' => [
                'directory' => $data['directory'],
                'files_analyzed' => $data['files_analyzed'],
                'total_errors' => $data['total_errors'],
                'total_warnings' => $data['total_warnings'],
            ],
            'top_errors' => $data['errors'],
            'top_warnings' => $data['warnings'],
            'recommendations' => $this->generateRecommendations($data),
        ];
    }

    /**
     * Generate recommendations based on analysis
     */
    private function generateRecommendations(array $data): array
    {
        $recommendations = [];

        if ($data['total_errors'] > 100) {
            $recommendations[] = "High error count detected ({$data['total_errors']}). Immediate investigation recommended.";
        }

        if ($data['total_warnings'] > 500) {
            $recommendations[] = "Excessive warnings detected ({$data['total_warnings']}). Review warning thresholds.";
        }

        // Analyze error patterns
        $criticalCount = count(array_filter(
            $data['errors'],
            fn ($e) => ($e['severity'] ?? null) === 'CRITICAL'
        ));

        if ($criticalCount > 0) {
            $recommendations[] = "{$criticalCount} CRITICAL errors found. Prioritize these issues.";
        }

        if (empty($recommendations)) {
            $recommendations[] = 'No major issues detected. Continue monitoring.';
        }

        return $recommendations;
    }
}
