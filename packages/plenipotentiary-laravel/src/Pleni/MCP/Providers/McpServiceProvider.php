<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Actions\CallToolAction;
use Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Operations\CallTool\CallToolGateway;
use Plenipotentiary\Laravel\Pleni\MCP\Contexts\Default\Operations\CallTool\CallToolOperation;
use Plenipotentiary\Laravel\Pleni\MCP\Shared\Support\AgentBudgetTracker;
use Plenipotentiary\Laravel\Pleni\MCP\Shared\Support\McpServerRegistry;
use Plenipotentiary\Laravel\Pleni\MCP\Shared\Transport\McpClient;

/**
 * Service Provider for MCP integration
 */
class McpServiceProvider extends ServiceProvider
{
    /**
     * Register MCP services
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../../../../config/mcp.php',
            'mcp'
        );

        // Register MCP server registry
        $this->app->singleton(McpServerRegistry::class, function ($app) {
            return new McpServerRegistry(
                config('mcp.servers', [])
            );
        });

        // Register MCP client
        $this->app->singleton(McpClient::class, function ($app) {
            return new McpClient(
                logger: $app->make('log')->channel(config('mcp.logging.channel', 'stack')),
            );
        });

        // Register agent budget tracker
        $this->app->singleton(AgentBudgetTracker::class, function ($app) {
            return new AgentBudgetTracker(
                budgetConfig: config('mcp.agent_budgets', [])
            );
        });

        // Register CallTool operation
        $this->app->bind(CallToolOperation::class, function ($app) {
            return new CallToolOperation(
                client: $app->make(McpClient::class),
                registry: $app->make(McpServerRegistry::class),
                logger: $app->make('log')->channel(config('mcp.logging.channel', 'stack')),
            );
        });

        // Register CallTool gateway
        $this->app->bind(CallToolGateway::class, function ($app) {
            return new CallToolGateway(
                operation: $app->make(CallToolOperation::class),
                budgetTracker: $app->make(AgentBudgetTracker::class),
                logger: $app->make('log')->channel(config('mcp.logging.channel', 'stack')),
            );
        });

        // Register CallTool action
        $this->app->bind(CallToolAction::class, function ($app) {
            return new CallToolAction(
                gateway: $app->make(CallToolGateway::class),
            );
        });
    }

    /**
     * Bootstrap MCP services
     */
    public function boot(): void
    {
        // Publish config
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../../../config/mcp.php' => config_path('mcp.php'),
            ], 'mcp-config');
        }
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            McpServerRegistry::class,
            McpClient::class,
            AgentBudgetTracker::class,
            CallToolOperation::class,
            CallToolGateway::class,
            CallToolAction::class,
        ];
    }
}
