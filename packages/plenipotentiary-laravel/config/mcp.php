<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Servers
    |--------------------------------------------------------------------------
    |
    | Configure MCP servers that agents can connect to.
    | Each server has a transport type (stdio or sse) and configuration.
    |
    */
    'servers' => [
        'filesystem' => [
            'transport' => 'stdio',
            'command' => 'npx',
            'args' => [
                '-y',
                '@modelcontextprotocol/server-filesystem',
                // Allowed directories (security: only these paths can be accessed)
                env('MCP_FILESYSTEM_ROOT', storage_path('logs')),
            ],
            'env' => [],
        ],

        // Example: Database MCP server (uncomment to use)
        // 'database' => [
        //     'transport' => 'stdio',
        //     'command' => 'npx',
        //     'args' => [
        //         '-y',
        //         '@modelcontextprotocol/server-sqlite',
        //     ],
        //     'env' => [
        //         'DB_PATH' => env('MCP_DATABASE_PATH', database_path('database.sqlite')),
        //     ],
        // ],

        // Example: Code search MCP server via SSE
        // 'code-search' => [
        //     'transport' => 'sse',
        //     'url' => env('MCP_CODE_SEARCH_URL', 'http://localhost:3000/sse'),
        //     'auth' => [
        //         'type' => 'bearer',
        //         'token' => env('MCP_CODE_SEARCH_TOKEN'),
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Budgets
    |--------------------------------------------------------------------------
    |
    | Configure budget limits per agent to prevent runaway costs.
    | Budgets reset daily.
    |
    */
    'agent_budgets' => [
        'default' => [
            'daily_limit' => (float) env('MCP_AGENT_DAILY_LIMIT', 10.00),  // $10 per day
            'per_call_limit' => (float) env('MCP_AGENT_CALL_LIMIT', 1.00), // $1 per call
        ],

        // Example: Custom budget for specific agents
        'log-analyzer' => [
            'daily_limit' => 5.00,   // $5 per day
            'per_call_limit' => 0.50, // $0.50 per call
        ],

        'campaign-optimizer' => [
            'daily_limit' => 50.00,  // $50 per day
            'per_call_limit' => 5.00, // $5 per call
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    |
    | Configure rate limits to prevent agent loops and abuse.
    |
    */
    'rate_limits' => [
        'default' => [
            'calls_per_minute' => (int) env('MCP_RATE_LIMIT_PER_MINUTE', 100),
            'calls_per_hour' => (int) env('MCP_RATE_LIMIT_PER_HOUR', 1000),
        ],

        // Example: Stricter limits for specific agents
        'experimental-agent' => [
            'calls_per_minute' => 10,
            'calls_per_hour' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for MCP operations
    |
    */
    'logging' => [
        'enabled' => (bool) env('MCP_LOGGING_ENABLED', true),
        'channel' => env('MCP_LOG_CHANNEL', 'stack'),
        'level' => env('MCP_LOG_LEVEL', 'info'),
    ],
];
