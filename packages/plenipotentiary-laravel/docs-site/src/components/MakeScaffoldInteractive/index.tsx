import React, { useState, useMemo } from "react";
import {
  Database,
  Zap,
  Wrench,
  Globe,
  CheckCircle,
  Terminal,
  FolderTree,
  FileCode,
  Play,
  Copy,
  Layers,
  Brain,
  Lightbulb,
  ShoppingCart,
  Search,
  Shield,
  Gauge,
  Activity,
  RotateCcw,
  AlertTriangle,
  Info,
} from "lucide-react";

type PatternType = "crud" | "operation" | "procedure" | "rest" | "mcp";

interface CrossCuttingConcern {
  id: string;
  name: string;
  icon: React.ComponentType<{ size?: number; className?: string }>;
  location: string;
  description: string;
  level: "global" | "provider" | "context" | "pattern";
}

interface RealWorldExample {
  id: string;
  name: string;
  icon: React.ComponentType<{ size?: number; className?: string }>;
  provider: string;
  domain: string;
  resource: string;
  pattern: PatternType;
  description: string;
  presetOptions: string[];
  useCase: string;
  crossCuttingConcerns: CrossCuttingConcern[];
}

interface ScaffoldOption {
  id: string;
  label: string;
  flag: string;
  description: string;
  folders: string[];
  filesGenerated: number;
}

interface Pattern {
  id: PatternType;
  name: string;
  icon: React.ComponentType<{ size?: number; className?: string }>;
  color: string;
  description: string;
  provider: string;
  domain: string;
  resource: string;
  requiredOptions: string[];
}

const patterns: Pattern[] = [
  {
    id: "crud",
    name: "CRUD Pattern",
    icon: Database,
    color: "blue",
    description: "Resource lifecycle management",
    provider: "Stripe",
    domain: "Billing",
    resource: "Customer",
    requiredOptions: [],
  },
  {
    id: "operation",
    name: "Operation Pattern",
    icon: Zap,
    color: "emerald",
    description: "Use case driven operations (search, calculate, verify)",
    provider: "OpenAI",
    domain: "Completions",
    resource: "GenerateText",
    requiredOptions: [],
  },
  {
    id: "procedure",
    name: "Procedure Pattern",
    icon: Wrench,
    color: "orange",
    description: "Simple RPC-style quick operations or prototyping",
    provider: "InternalAPI",
    domain: "Admin",
    resource: "SendAlert",
    requiredOptions: [],
  },
  {
    id: "rest",
    name: "REST Pattern",
    icon: Globe,
    color: "cyan",
    description: "Saloon Request/Response for clean RESTful APIs",
    provider: "GitHub",
    domain: "API",
    resource: "Repositories",
    requiredOptions: [],
  },
  {
    id: "mcp",
    name: "MCP Proxy Pattern (Niche)",
    icon: Brain,
    color: "purple",
    description: "Proxy MCP servers through Laravel for controlled AI tool access with budget/rate limits",
    provider: "DatabaseMCP",
    domain: "Proxy",
    resource: "QueryTool",
    requiredOptions: [],
  },
];

const realWorldExamples: RealWorldExample[] = [
  {
    id: "google-ads-campaign",
    name: "Google Ads Campaign Sync",
    icon: ShoppingCart,
    provider: "Google",
    domain: "Ads",
    resource: "Campaign",
    pattern: "crud",
    description:
      "Google Ads SPAG Campaign sync - pauses/resumes ad groups based on stock availability. CRUD Pattern with Actions, Commands and Queue Jobs, Test Suite.",
    presetOptions: ["actions", "commands", "jobs", "tests"],
    useCase:
      "Assumes local domain already has a database representation of the campaign structure",
    crossCuttingConcerns: [
      {
        id: "rate-limit",
        name: "Rate Limiting",
        icon: Gauge,
        location: "Pleni/Policies/RateLimitPolicy.php",
        description:
          "Google Ads API has strict rate limits (10K requests/day). Global policy prevents exceeding limits.",
        level: "global",
      },
      {
        id: "logging",
        name: "Logging",
        icon: Activity,
        location: "Pleni/Policies/LoggingPolicy.php",
        description:
          "Track all campaign pause/resume operations for compliance audit trail.",
        level: "global",
      },
      {
        id: "error-mapping",
        name: "Error Mapping",
        icon: AlertTriangle,
        location: "Pleni/Google/Ads/Shared/Support/GoogleAdsErrorMapper.php",
        description:
          "Maps Google Ads SDK exceptions to domain errors (e.g., CAMPAIGN_NOT_FOUND, BUDGET_EXHAUSTED).",
        level: "provider",
      },
      {
        id: "idempotency",
        name: "Idempotency",
        icon: RotateCcw,
        location:
          "Pleni/Google/Ads/Contexts/Search/Campaign/Support/CampaignIdempotencyHints.php",
        description:
          "Prevent duplicate campaign updates when job is retried. Uses campaign ID + operation hash.",
        level: "context",
      },
    ],
  },
  {
    id: "ebay-search",
    name: "eBay Browse API",
    icon: Search,
    provider: "eBay",
    domain: "Browse",
    resource: "SearchItems",
    pattern: "operation",
    description:
      "Backend API that drives a custom JS frontend. Operation Pattern with HTTP Controllers and test suite.",
    presetOptions: ["controller", "tests"],
    useCase: "RESTful API endpoints for frontend consumption",
    crossCuttingConcerns: [
      {
        id: "retry",
        name: "Retry with Backoff",
        icon: RotateCcw,
        location: "Pleni/Policies/RetryBackoffPolicy.php",
        description:
          "eBay API can be flaky. Automatically retry failed searches with exponential backoff (3 attempts).",
        level: "global",
      },
      {
        id: "logging",
        name: "Logging",
        icon: Activity,
        location: "Pleni/Policies/LoggingPolicy.php",
        description:
          "Log search queries and response times for performance monitoring.",
        level: "global",
      },
      {
        id: "error-mapping",
        name: "Error Mapping",
        icon: AlertTriangle,
        location: "Pleni/eBay/Browse/Shared/Support/eBayErrorMapper.php",
        description:
          "Normalizes eBay API errors (INVALID_QUERY, CATEGORY_NOT_FOUND) to domain exceptions.",
        level: "provider",
      },
    ],
  },
  {
    id: "internal-alert",
    name: "Internal Admin Alerts",
    icon: Wrench,
    provider: "InternalAPI",
    domain: "Admin",
    resource: "SendAlert",
    pattern: "procedure",
    description:
      "Quick internal tool for sending system alerts to Slack/Teams. Procedure Pattern with Artisan command for one-off admin tasks.",
    presetOptions: ["commands"],
    useCase:
      "Rapid prototyping for internal tools without full CRUD scaffolding",
    crossCuttingConcerns: [
      {
        id: "logging",
        name: "Logging",
        icon: Activity,
        location: "Pleni/Policies/LoggingPolicy.php",
        description:
          "Track all alert sends for audit trail (who sent what alert when).",
        level: "global",
      },
      {
        id: "error-mapping",
        name: "Error Mapping",
        icon: AlertTriangle,
        location:
          "Pleni/InternalAPI/Admin/Shared/Support/InternalAPIErrorMapper.php",
        description:
          "Maps internal API errors (INVALID_CHANNEL, RATE_LIMITED) to domain exceptions.",
        level: "provider",
      },
    ],
  },
  {
    id: "github-repos",
    name: "GitHub Repository Integration",
    icon: Globe,
    provider: "GitHub",
    domain: "API",
    resource: "Repositories",
    pattern: "rest",
    description:
      "GitHub API integration with 50+ endpoints. REST Pattern uses Saloon's Request/Connector pattern with dedicated request classes for each endpoint (SearchRepos, GetRepo, CreateRepo, UpdateRepo, etc.). Clean, type-safe API calls.",
    presetOptions: ["requests", "tests"],
    useCase:
      "When you need Saloon's familiar Request pattern with optional Gateway features (policies, validation, persistence)",
    crossCuttingConcerns: [
      {
        id: "rate-limit",
        name: "Rate Limiting",
        icon: Gauge,
        location: "Pleni/Policies/RateLimitPolicy.php",
        description:
          "GitHub API rate limits: 5,000/hour authenticated, 60/hour unauthenticated. Global policy tracks limits.",
        level: "global",
      },
      {
        id: "retry",
        name: "Retry with Backoff",
        icon: RotateCcw,
        location: "Pleni/Policies/RetryBackoffPolicy.php",
        description:
          "Retry failed requests on 502/503/504 errors with exponential backoff.",
        level: "global",
      },
      {
        id: "logging",
        name: "Logging",
        icon: Activity,
        location: "Pleni/Policies/LoggingPolicy.php",
        description:
          "Log all API calls for debugging and monitoring rate limit consumption.",
        level: "global",
      },
      {
        id: "error-mapping",
        name: "Error Mapping",
        icon: AlertTriangle,
        location: "Pleni/GitHub/API/Shared/Support/GitHubErrorMapper.php",
        description:
          "Maps GitHub API errors (NOT_FOUND, FORBIDDEN, VALIDATION_FAILED) to domain exceptions.",
        level: "provider",
      },
    ],
  },
  {
    id: "ai-log-analyzer",
    name: "AI Log Analyzer Tool Proxy",
    icon: Brain,
    provider: "FilesystemMCP",
    domain: "Proxy",
    resource: "ReadLogFileTool",
    pattern: "mcp",
    description:
      "Proxy the Filesystem MCP server through your Laravel API. Claude/GPT calls YOUR endpoint, which forwards to the real Filesystem MCP server with budget tracking, rate limits, and audit logs to prevent runaway costs from AI agents reading logs.",
    presetOptions: ["actions", "policies", "mcp-server", "audit", "tests"],
    useCase:
      "When AI agents need controlled access to existing Filesystem MCP tools with strict budget limits, rate control, and complete audit trails",
    crossCuttingConcerns: [
      {
        id: "agent-budget",
        name: "MCP Proxy Budget Policy",
        icon: Gauge,
        location: "Pleni/MCP/Shared/Policies/McpProxyBudgetPolicy.php",
        description:
          "Prevent runaway AI costs when proxying MCP tool calls. Track and limit total calls per day/hour.",
        level: "pattern",
      },
      {
        id: "agent-rate-limit",
        name: "MCP Proxy Rate Limit",
        icon: Shield,
        location: "Pleni/MCP/Shared/Policies/McpProxyRateLimitPolicy.php",
        description:
          "Prevent AI agent abuse when proxying MCP servers. Max 100 tool calls per minute, 1000 per hour.",
        level: "pattern",
      },
      {
        id: "logging",
        name: "Logging",
        icon: Activity,
        location: "Pleni/Policies/LoggingPolicy.php",
        description:
          "Log every MCP tool call proxied through your Laravel API: which tool, which agent, when, and what data was accessed.",
        level: "global",
      },
      {
        id: "audit-policy",
        name: "MCP Proxy Audit Policy",
        icon: Shield,
        location: "Pleni/MCP/Shared/Policies/McpProxyAuditPolicy.php",
        description:
          "Full audit trail of all MCP tool calls proxied through Laravel for compliance, GDPR, and security review.",
        level: "pattern",
      },
    ],
  },
  {
    id: "ai-customer-insight",
    name: "Customer Database MCP Proxy",
    icon: Brain,
    provider: "DatabaseMCP",
    domain: "Proxy",
    resource: "QueryCustomerTool",
    pattern: "mcp",
    description:
      "Proxy a custom Database MCP server through your Laravel API. Claude/GPT calls YOUR endpoint (POST /api/mcp/database/query), which forwards to the real Database MCP server with budget enforcement, query limits, and complete audit logs of all customer data access.",
    presetOptions: [
      "actions",
      "jobs",
      "policies",
      "mcp-server",
      "audit",
      "tests",
    ],
    useCase:
      "When AI agents need controlled access to high-stakes customer data via MCP tools with strict budget limits, rate control, and GDPR-compliant audit trails",
    crossCuttingConcerns: [
      {
        id: "agent-budget",
        name: "MCP Proxy Budget Policy",
        icon: Gauge,
        location: "Pleni/MCP/Shared/Policies/McpProxyBudgetPolicy.php",
        description:
          "Complex multi-step AI workflows can make hundreds of MCP tool calls. Enforce strict budget limits on proxied requests.",
        level: "pattern",
      },
      {
        id: "agent-rate-limit",
        name: "MCP Proxy Rate Limit",
        icon: Shield,
        location: "Pleni/MCP/Shared/Policies/McpProxyRateLimitPolicy.php",
        description:
          "Multi-server AI workflows require multiple tool calls. Prevent abuse by limiting proxied MCP requests per minute/hour.",
        level: "pattern",
      },
      {
        id: "logging",
        name: "Logging",
        icon: Activity,
        location: "Pleni/Policies/LoggingPolicy.php",
        description:
          "Log all database queries proxied through your MCP Gateway: which customer data was accessed, when, and by which AI agent.",
        level: "global",
      },
      {
        id: "audit-policy",
        name: "MCP Proxy Audit Policy",
        icon: Shield,
        location: "Pleni/MCP/Shared/Policies/McpProxyAuditPolicy.php",
        description:
          "Track all customer data access by AI agents via MCP proxy for GDPR compliance and security audits.",
        level: "pattern",
      },
      {
        id: "idempotency",
        name: "Idempotency",
        icon: RotateCcw,
        location:
          "Pleni/DatabaseMCP/Proxy/QueryCustomerTool/Support/QueryIdempotencyHints.php",
        description:
          "Prevent duplicate MCP tool calls when AI workflow is retried. Cache proxied results by customer ID + query hash.",
        level: "context",
      },
    ],
  },
];

const scaffoldOptions: ScaffoldOption[] = [
  {
    id: "actions",
    label: "Laravel Actions",
    flag: "--with-actions",
    description: "Generate Action classes for business logic",
    folders: ["Actions/"],
    filesGenerated: 3,
  },
  {
    id: "repository",
    label: "Repository Layer",
    flag: "--with-repository",
    description: "Add persistence repositories (Eloquent, Mongo, etc.)",
    folders: ["Repository/"],
    filesGenerated: 2,
  },
  {
    id: "commands",
    label: "Artisan Commands",
    flag: "--with-commands",
    description: "Create CLI commands for operations",
    folders: [],
    filesGenerated: 2,
  },
  {
    id: "jobs",
    label: "Queue Jobs",
    flag: "--with-jobs",
    description: "Add queueable job classes",
    folders: [],
    filesGenerated: 3,
  },
  {
    id: "controller",
    label: "HTTP Controllers",
    flag: "--with-controller",
    description: "Generate API controllers",
    folders: [],
    filesGenerated: 1,
  },
  {
    id: "requests",
    label: "Form Requests",
    flag: "--with-requests",
    description: "Add validation request classes",
    folders: [],
    filesGenerated: 2,
  },
  {
    id: "tests",
    label: "Test Suite",
    flag: "--with-tests",
    description: "Generate unit, feature, and integration tests",
    folders: ["Tests/"],
    filesGenerated: 5,
  },
  {
    id: "migrations",
    label: "Database Migrations",
    flag: "--with-migrations",
    description: "Create database migration files",
    folders: [],
    filesGenerated: 1,
  },
  {
    id: "factories",
    label: "Model Factories",
    flag: "--with-factories",
    description: "Add factory classes for testing",
    folders: [],
    filesGenerated: 1,
  },
  {
    id: "seeders",
    label: "Database Seeders",
    flag: "--with-seeders",
    description: "Generate seeder classes",
    folders: [],
    filesGenerated: 1,
  },
  {
    id: "policies",
    label: "Policy Chain",
    flag: "--with-policies",
    description: "Generate budget, rate limit, and permission policies",
    folders: ["Policies/"],
    filesGenerated: 3,
  },
  {
    id: "mcp-server",
    label: "MCP Proxy Adapter",
    flag: "--with-mcp-proxy",
    description: "Generate adapter to proxy requests to existing MCP servers (stdio/SSE)",
    folders: ["MCP/"],
    filesGenerated: 2,
  },
  {
    id: "audit",
    label: "Audit Trail",
    flag: "--with-audit",
    description: "Generate audit logging for AI tool access tracking",
    folders: ["Audit/"],
    filesGenerated: 2,
  },
];

export default function MakeScaffoldInteractive() {
  const [selectedPattern, setSelectedPattern] = useState<PatternType>("crud");
  const [selectedOptions, setSelectedOptions] = useState<Set<string>>(
    new Set()
  );
  const [copied, setCopied] = useState(false);
  const [activeExample, setActiveExample] = useState<string | null>(null);
  const [customProvider, setCustomProvider] = useState("");
  const [customDomain, setCustomDomain] = useState("");
  const [customResource, setCustomResource] = useState("");
  const patternSectionRef = React.useRef<HTMLDivElement>(null);

  const currentPattern =
    patterns.find((p) => p.id === selectedPattern) || patterns[0];

  const loadExample = (example: RealWorldExample) => {
    setSelectedPattern(example.pattern);
    setSelectedOptions(new Set(example.presetOptions));
    setCustomProvider(example.provider);
    setCustomDomain(example.domain);
    setCustomResource(example.resource);
    setActiveExample(example.id);

    // Scroll to pattern section
    setTimeout(() => {
      patternSectionRef.current?.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }, 100);
  };

  const resetToManual = () => {
    setCustomProvider("");
    setCustomDomain("");
    setCustomResource("");
    setActiveExample(null);
  };

  const handlePatternChange = (patternId: PatternType) => {
    setSelectedPattern(patternId);
    setCustomProvider("");
    setCustomDomain("");
    setCustomResource("");
    setActiveExample(null);
    setSelectedOptions(new Set());
  };

  const toggleOption = (optionId: string) => {
    resetToManual();
    const newSelected = new Set(selectedOptions);
    if (newSelected.has(optionId)) {
      newSelected.delete(optionId);
    } else {
      newSelected.add(optionId);
    }
    setSelectedOptions(newSelected);
  };

  const selectAll = () => {
    setSelectedOptions(new Set(scaffoldOptions.map((opt) => opt.id)));
  };

  const clearAll = () => {
    setSelectedOptions(new Set());
  };

  const generatedCommand = useMemo(() => {
    const provider = customProvider || currentPattern.provider;
    const domain = customDomain || currentPattern.domain;
    const resource = customResource || currentPattern.resource;

    const baseFlags = [
      `--provider=${provider}`,
      `--domain=${domain}`,
      `--resource=${resource}`,
    ];

    const optionFlags = Array.from(selectedOptions)
      .map((id) => scaffoldOptions.find((opt) => opt.id === id)?.flag)
      .filter(Boolean);

    const allFlags = [...baseFlags, ...optionFlags];

    return `php artisan pleni:make:${selectedPattern} \\\n  ${allFlags.join(
      " \\\n  "
    )}`;
  }, [
    selectedPattern,
    selectedOptions,
    currentPattern,
    customProvider,
    customDomain,
    customResource,
  ]);

  const totalFiles = useMemo(() => {
    // Base files (DTO, Gateway, Adapter operations)
    let base =
      selectedPattern === "crud" ? 8 : selectedPattern === "mcp" ? 6 : 4;

    const optionalFiles = Array.from(selectedOptions)
      .map(
        (id) =>
          scaffoldOptions.find((opt) => opt.id === id)?.filesGenerated || 0
      )
      .reduce((sum, count) => sum + count, 0);

    return base + optionalFiles;
  }, [selectedPattern, selectedOptions]);

  const copyCommand = () => {
    navigator.clipboard.writeText(generatedCommand);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const folderStructure = useMemo(() => {
    const basePath =
      selectedPattern === "mcp"
        ? "Pleni/MCP/{Server}/"
        : selectedPattern === "procedure"
        ? "Pleni/{Provider}/{Domain}/Shared/Procedure/"
        : selectedPattern === "rest"
        ? "Pleni/{Provider}/{Domain}/Shared/Transfer/Rest/"
        : "Pleni/{Provider}/{Domain}/Contexts/{Context}/{Resource}/";

    const baseFolders =
      selectedPattern === "crud"
        ? [
            "├── DTO/",
            "│   └── {Resource}CanonicalDTO.php (includes fromArray() factory method)",
            "├── Selector/",
            "│   └── {Resource}Selector.php",
            "├── Gateway/",
            "│   └── {Resource}CrudGateway.php",
            "└── Adapter/",
            "    ├── {Resource}CrudAdapter.php",
            "    ├── {Resource}Create.php",
            "    ├── {Resource}Read.php",
            "    ├── {Resource}ReadMany.php",
            "    ├── {Resource}Update.php",
            "    └── {Resource}Delete.php",
          ]
        : selectedPattern === "mcp"
        ? [
            "├── Gateway/",
            "│   └── {Server}McpProxyGateway.php",
            "├── Adapter/",
            "│   └── {Server}McpAdapter.php",
            "├── Policies/",
            "│   ├── BudgetPolicy.php",
            "│   └── RateLimitPolicy.php",
            "├── Support/",
            "│   └── AuditLogger.php",
            "└── Http/Controllers/",
            "    └── {Server}McpController.php",
          ]
        : selectedPattern === "operation"
        ? [
            "Operations live in:",
            "Pleni/{Provider}/{Domain}/Contexts/Default/Operations/{UseCase}/",
            "├── {UseCase}Operation.php",
            "├── {UseCase}DTO.php",
            "└── {UseCase}Result.php",
            "│",
            "Actions live in:",
            "Pleni/{Provider}/{Domain}/Contexts/Default/Operations/Actions/",
            "└── {UseCase}Action.php",
            "│",
            "Shared infrastructure:",
            "Pleni/{Provider}/{Domain}/Shared/Transfer/",
            "├── {Provider}{Domain}OperationGateway.php",
            "└── {Provider}{Domain}OperationAdapter.php",
          ]
        : selectedPattern === "rest"
        ? [
            "├── {Provider}{Domain}RestConnector.php",
            "│",
            "Requests live in:",
            "Pleni/{Provider}/{Domain}/Contexts/Default/{Resource}/Requests/",
            "├── CreatePaymentRequest.php",
            "├── GetCustomerRequest.php",
            "├── ProcessRefundRequest.php",
            "└── UpdatePaymentRequest.php",
            "│",
            "Optional (Gateway pattern):",
            "├── {Provider}{Domain}RestGateway.php",
            "└── {Provider}{Domain}RestAdapter.php",
          ]
        : [
            "Procedures live in:",
            "Pleni/{Provider}/{Domain}/Contexts/Default/Procedures/",
            "├── SearchItems.php",
            "├── SendNotification.php",
            "└── ProcessRefund.php",
            "│",
            "Shared infrastructure:",
            "Pleni/{Provider}/{Domain}/Shared/Procedure/",
            "├── {Provider}{Domain}ProcedureAdapter.php",
            "├── {Provider}{Domain}ProcedureGateway.php",
            "└── {Provider}{Domain}ProcedureConnector.php",
          ];

    const optionalFolders: string[] = [];
    selectedOptions.forEach((id) => {
      const option = scaffoldOptions.find((opt) => opt.id === id);
      if (option) {
        option.folders.forEach((folder) => {
          if (!optionalFolders.includes(folder)) {
            optionalFolders.push(folder);
          }
        });
      }
    });

    return {
      basePath,
      baseFolders,
      optionalFolders,
    };
  }, [selectedPattern, selectedOptions]);

  return (
    <div className="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-16 px-4">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center justify-center gap-3 mb-4">
            <Terminal className="w-10 h-10 text-emerald-600" />
            <h2 className="text-3xl font-bold text-slate-900 m-0">
              Scaffolding
            </h2>
          </div>
          <p className="text-lg text-slate-600 max-w-3xl mx-auto">
            Generate the exact artisan command to scaffold your integration
            structure
          </p>
        </div>

        {/* Quick Start Notice */}
        <div className="mb-10 p-6 bg-gradient-to-r from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-2xl shadow-md">
          <div className="flex gap-4">
            <Info className="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" />
            <div>
              <h3 className="font-bold text-slate-900 mb-2">
                What Scaffolding Generates
              </h3>
              <p className="text-slate-700 leading-relaxed mb-3">
                The scaffold creates your <strong>folder structure</strong>,{" "}
                <strong>stub files</strong>, and <strong>base classes</strong>.
                You'll then follow the Developer Workflow to:
              </p>
              <ul className="list-none space-y-2 pl-0">
                <li className="flex items-start gap-2">
                  <CheckCircle className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                  <span className="text-slate-700">
                    <strong>Write your adapter files</strong> – Implement the
                    actual HTTP requests using Saloon (or other transport)
                  </span>
                </li>
                <li className="flex items-start gap-2">
                  <CheckCircle className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                  <span className="text-slate-700">
                    <strong>Write your integration logic</strong> – Map API
                    responses to your DTOs, handle errors, add business logic
                  </span>
                </li>
                <li className="flex items-start gap-2">
                  <CheckCircle className="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                  <span className="text-slate-700">
                    <strong>Configure cross-cutting concerns</strong> – Add
                    policies, queuing, caching, logging as needed
                  </span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        {/* Repository Pattern Note */}
        <div className="mb-10 p-6 bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-r-2xl shadow-md">
          <div className="flex gap-4">
            <Info className="w-6 h-6 text-amber-600 flex-shrink-0 mt-1" />
            <div>
              <h4 className="font-bold text-slate-900 mb-2">
                A Note About the Repository Pattern
              </h4>
              <p className="text-sm text-slate-700 leading-relaxed mb-3">
                Pleni offers a Repository layer but there is no mandate. If your
                use case is simple and you're happy with direct Eloquent usage,
                you do not need to adopt the Repository pattern.
              </p>
              <p className="text-sm text-slate-700 leading-relaxed mb-3">
                But bear in mind:{" "}
                <strong>not all API results are relational</strong>. This is one
                of the rare cases when you might actually want to use different
                persistence layers for different types of analysis:
              </p>
              <ul className="list-none space-y-2 pl-0 text-sm text-slate-700">
                <li className="flex items-start gap-2">
                  <span className="text-amber-600 font-bold">•</span>
                  <span>
                    <strong>Time-series data:</strong> API analytics or metrics
                    are better stored in InfluxDB or TimescaleDB for efficient
                    time-based queries
                  </span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-amber-600 font-bold">•</span>
                  <span>
                    <strong>Search results:</strong> Product catalogs or eBay
                    listings benefit from Elasticsearch for full-text search and
                    faceted filtering
                  </span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-amber-600 font-bold">•</span>
                  <span>
                    <strong>Document-based data:</strong> OpenAI completions or
                    unstructured API responses fit better in MongoDB or DynamoDB
                  </span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-amber-600 font-bold">•</span>
                  <span>
                    <strong>Session/cache data:</strong> Temporary API results
                    or rate limit tracking work well in Redis for fast access
                    and automatic expiration
                  </span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="text-amber-600 font-bold">•</span>
                  <span>
                    <strong>File-based storage:</strong> Large responses or blob
                    data (images, PDFs) may be better stored in S3 with metadata
                    in your database
                  </span>
                </li>
              </ul>
              <p className="text-sm text-slate-700 leading-relaxed mt-3">
                The Repository pattern gives you the flexibility to swap storage
                layers without changing your application code—exactly what you
                need when different data types require different analysis tools.
              </p>
            </div>
          </div>
        </div>

        {/* Pattern Selection */}
        <div ref={patternSectionRef} className="mb-8">
          <div className="flex items-center gap-3 mb-4">
            <Layers className="w-6 h-6 text-emerald-600" />
            <h3 className="text-2xl font-bold text-slate-900 m-0">
              Step 1: Choose Your Pattern
            </h3>
          </div>
          <div className="grid md:grid-cols-3 lg:grid-cols-5 gap-4">
            {patterns.map((pattern) => {
              const Icon = pattern.icon;
              const isActive = selectedPattern === pattern.id;
              return (
                <button
                  key={pattern.id}
                  onClick={() => handlePatternChange(pattern.id)}
                  className={`p-5 rounded-2xl transition-all duration-300 text-left ${
                    isActive
                      ? "bg-white shadow-xl border-2 border-teal-500 scale-105"
                      : "bg-white/70 border-2 border-slate-200 hover:border-teal-300 hover:shadow-lg"
                  }`}
                >
                  <div className="flex items-center gap-3 mb-3">
                    <div
                      className={`w-12 h-12 rounded-lg flex items-center justify-center ${
                        isActive
                          ? "bg-gradient-to-br from-emerald-400 to-teal-600"
                          : "bg-slate-100"
                      }`}
                    >
                      <Icon
                        className={`w-6 h-6 ${
                          isActive ? "text-white" : "text-slate-600"
                        }`}
                      />
                    </div>
                  </div>
                  <div className="font-bold text-slate-900 mb-2">
                    {pattern.name}
                  </div>
                  <div className="text-sm text-slate-600">
                    {pattern.description}
                  </div>
                </button>
              );
            })}
          </div>
        </div>

        {/* Main Content Grid */}
        <div className="grid lg:grid-cols-2 gap-8">
          {/* Left Column - Options */}
          <div>
            <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
              <div className="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 px-4 sm:px-6 py-4">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                  <div className="flex items-center gap-3">
                    <FileCode className="w-5 h-5 sm:w-6 sm:h-6 text-white flex-shrink-0" />
                    <h3 className="text-lg sm:text-xl font-bold text-white m-0">
                      Step 2: Choose Optional Components
                    </h3>
                  </div>
                  <div className="flex gap-2">
                    <button
                      onClick={selectAll}
                      className="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-xs sm:text-sm font-medium text-white transition-colors"
                    >
                      Select All
                    </button>
                    <button
                      onClick={clearAll}
                      className="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-xs sm:text-sm font-medium text-white transition-colors"
                    >
                      Clear
                    </button>
                  </div>
                </div>
              </div>
              <div className="p-4 sm:p-6">
                <div className="space-y-2 sm:space-y-3">
                  {scaffoldOptions.map((option) => {
                    const isSelected = selectedOptions.has(option.id);
                    return (
                      <button
                        key={option.id}
                        onClick={() => toggleOption(option.id)}
                        className={`w-full text-left p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 transition-all duration-200 ${
                          isSelected
                            ? "bg-emerald-50 border-emerald-500 shadow-md"
                            : "bg-slate-50 border-slate-200 hover:border-emerald-300 hover:bg-slate-100"
                        }`}
                      >
                        <div className="flex items-start gap-2 sm:gap-3">
                          <div
                            className={`w-5 h-5 sm:w-6 sm:h-6 rounded-md border-2 flex items-center justify-center flex-shrink-0 mt-0.5 ${
                              isSelected
                                ? "bg-emerald-500 border-emerald-500"
                                : "bg-white border-slate-300"
                            }`}
                          >
                            {isSelected && (
                              <CheckCircle className="w-3 h-3 sm:w-4 sm:h-4 text-white" />
                            )}
                          </div>
                          <div className="flex-1 min-w-0">
                            <div className="font-semibold text-sm sm:text-base text-slate-900 mb-1">
                              {option.label}
                            </div>
                            <div className="text-xs sm:text-sm text-slate-600 mb-2">
                              {option.description}
                            </div>
                            <div className="flex items-center gap-2 text-xs flex-wrap">
                              <code className="px-1.5 sm:px-2 py-0.5 sm:py-1 bg-slate-200 rounded text-slate-700 font-mono text-xs break-all">
                                {option.flag}
                              </code>
                              <span className="text-slate-500 whitespace-nowrap">
                                +{option.filesGenerated} files
                              </span>
                            </div>
                          </div>
                        </div>
                      </button>
                    );
                  })}
                </div>
              </div>
            </div>
          </div>

          {/* Right Column - Output */}
          <div className="space-y-6">
            {/* Generated Command */}
            <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
              <div className="bg-gradient-to-r from-slate-700 via-slate-800 to-slate-900 px-6 py-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <Terminal className="w-6 h-6 text-cyan-400" />
                    <h3 className="text-xl font-bold text-white m-0">
                      Generated Command
                    </h3>
                  </div>
                  <button
                    onClick={copyCommand}
                    className="flex items-center gap-2 px-4 py-2 bg-cyan-500 hover:bg-cyan-600 rounded-lg text-white font-medium transition-colors"
                  >
                    <Copy className="w-4 h-4" />
                    {copied ? "Copied!" : "Copy"}
                  </button>
                </div>
              </div>
              <div className="p-6">
                <div className="bg-slate-900 rounded-2xl overflow-hidden shadow-lg">
                  <div className="flex items-center gap-2 px-5 py-3 bg-slate-800 border-b border-slate-700">
                    <div className="flex gap-2">
                      <div className="w-3 h-3 rounded-full bg-red-500"></div>
                      <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                      <div className="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <span className="text-xs text-slate-400 ml-3 font-mono">
                      terminal
                    </span>
                  </div>
                  <pre className="p-6 overflow-x-auto text-sm leading-loose m-0">
                    <code className="text-emerald-400 font-mono whitespace-pre">
                      {generatedCommand}
                    </code>
                  </pre>
                </div>
              </div>
            </div>

            {/* Folder Structure Preview */}
            <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
              <div className="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 px-6 py-4">
                <div className="flex items-center gap-3">
                  <FolderTree className="w-6 h-6 text-white" />
                  <h3 className="text-xl font-bold text-white m-0">
                    Folder Structure Preview
                  </h3>
                </div>
              </div>
              <div className="p-6">
                <div className="bg-slate-900 rounded-2xl overflow-hidden shadow-lg">
                  <div className="flex items-center gap-2 px-5 py-3 bg-slate-800 border-b border-slate-700">
                    <div className="flex gap-2">
                      <div className="w-3 h-3 rounded-full bg-red-500"></div>
                      <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                      <div className="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <span className="text-xs text-slate-400 ml-3 font-mono">
                      structure
                    </span>
                  </div>
                  <pre className="p-6 overflow-x-auto text-sm leading-relaxed m-0">
                    <code className="text-slate-300 font-mono">
                      <div className="text-cyan-400 mb-2">
                        {folderStructure.basePath}
                      </div>
                      {folderStructure.baseFolders.map((folder, idx) => (
                        <div key={idx} className="text-slate-300">
                          {folder}
                        </div>
                      ))}
                      {folderStructure.optionalFolders.length > 0 && (
                        <>
                          <div className="text-emerald-400 mt-2">
                            ├── Optional/
                          </div>
                          {folderStructure.optionalFolders.map(
                            (folder, idx) => (
                              <div key={idx} className="text-emerald-400">
                                │ {folder}
                              </div>
                            )
                          )}
                        </>
                      )}
                    </code>
                  </pre>
                </div>
              </div>
            </div>

            {/* Context Explanation */}
            <div className="mt-6 p-6 bg-gradient-to-r from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-2xl shadow-md">
              <div className="flex gap-4">
                <Info className="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" />
                <div>
                  <h3 className="font-bold text-slate-900 mb-2">
                    What is a Context?
                  </h3>
                  <p className="text-sm text-slate-700 leading-relaxed mb-3">
                    A context is a way of grouping resources that "work
                    differently depending on how you use them." For example,
                    Google Ads has multiple campaign types (Search, Display,
                    Shopping, Performance Max) - they're all "campaigns," but
                    each has different rules and setup.
                  </p>
                  <p className="text-sm text-slate-700 leading-relaxed mb-3">
                    You can model your "Search" strategy as Campaigns, Ad
                    Groups, and Ads in one context called "Search." Create a new
                    context for Shopping campaigns with different rules and
                    DTOs. They all share the same stable Gateway contract,
                    keeping your app code clean.
                  </p>
                  <p className="text-sm text-slate-700 leading-relaxed">
                    <strong>Contexts are optional</strong> - use them when you
                    need logical separation. For folder consistency, if no
                    context is specified, a "Default" context will be used.
                  </p>
                </div>
              </div>
            </div>

            {/* Cross-Cutting Concerns */}
            {activeExample &&
              (() => {
                const example = realWorldExamples.find(
                  (ex) => ex.id === activeExample
                );
                if (!example || example.crossCuttingConcerns.length === 0)
                  return null;

                const getLevelStyles = (level: string) => {
                  switch (level) {
                    case "global":
                      return {
                        container:
                          "p-4 rounded-2xl border-2 border-emerald-200 bg-emerald-50",
                        icon: "w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center flex-shrink-0",
                        title: "font-bold text-emerald-900 m-0",
                        badge:
                          "text-xs px-2 py-1 bg-emerald-200 text-emerald-800 rounded-full font-semibold uppercase",
                        description: "text-sm text-emerald-800 mb-2",
                        fileIcon: "w-4 h-4 text-emerald-600",
                        fileCode:
                          "text-xs font-mono text-emerald-700 bg-emerald-100 px-2 py-1 rounded",
                      };
                    case "provider":
                      return {
                        container:
                          "p-4 rounded-2xl border-2 border-blue-200 bg-blue-50",
                        icon: "w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center flex-shrink-0",
                        title: "font-bold text-blue-900 m-0",
                        badge:
                          "text-xs px-2 py-1 bg-blue-200 text-blue-800 rounded-full font-semibold uppercase",
                        description: "text-sm text-blue-800 mb-2",
                        fileIcon: "w-4 h-4 text-blue-600",
                        fileCode:
                          "text-xs font-mono text-blue-700 bg-blue-100 px-2 py-1 rounded",
                      };
                    case "context":
                      return {
                        container:
                          "p-4 rounded-2xl border-2 border-purple-200 bg-purple-50",
                        icon: "w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center flex-shrink-0",
                        title: "font-bold text-purple-900 m-0",
                        badge:
                          "text-xs px-2 py-1 bg-purple-200 text-purple-800 rounded-full font-semibold uppercase",
                        description: "text-sm text-purple-800 mb-2",
                        fileIcon: "w-4 h-4 text-purple-600",
                        fileCode:
                          "text-xs font-mono text-purple-700 bg-purple-100 px-2 py-1 rounded",
                      };
                    case "pattern":
                      return {
                        container:
                          "p-4 rounded-2xl border-2 border-cyan-200 bg-cyan-50",
                        icon: "w-10 h-10 rounded-lg bg-cyan-500 flex items-center justify-center flex-shrink-0",
                        title: "font-bold text-cyan-900 m-0",
                        badge:
                          "text-xs px-2 py-1 bg-cyan-200 text-cyan-800 rounded-full font-semibold uppercase",
                        description: "text-sm text-cyan-800 mb-2",
                        fileIcon: "w-4 h-4 text-cyan-600",
                        fileCode:
                          "text-xs font-mono text-cyan-700 bg-cyan-100 px-2 py-1 rounded",
                      };
                    default:
                      return {
                        container:
                          "p-4 rounded-2xl border-2 border-slate-200 bg-slate-50",
                        icon: "w-10 h-10 rounded-lg bg-slate-500 flex items-center justify-center flex-shrink-0",
                        title: "font-bold text-slate-900 m-0",
                        badge:
                          "text-xs px-2 py-1 bg-slate-200 text-slate-800 rounded-full font-semibold uppercase",
                        description: "text-sm text-slate-800 mb-2",
                        fileIcon: "w-4 h-4 text-slate-600",
                        fileCode:
                          "text-xs font-mono text-slate-700 bg-slate-100 px-2 py-1 rounded",
                      };
                  }
                };

                return (
                  <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
                    <div className="bg-gradient-to-r from-purple-500 via-purple-600 to-indigo-600 px-6 py-4">
                      <div className="flex items-center gap-3">
                        <Shield className="w-6 h-6 text-white" />
                        <h3 className="text-xl font-bold text-white m-0">
                          Cross-Cutting Concerns
                        </h3>
                      </div>
                      <p className="text-sm text-purple-100 mt-2 mb-0">
                        Policies and infrastructure applied to this integration
                      </p>
                    </div>
                    <div className="p-6">
                      {/* Level Legend */}
                      <div className="mb-6 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <div className="text-sm font-semibold text-slate-700 mb-3">
                          Scope Levels:
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                          <div className="flex items-center gap-2">
                            <div className="w-3 h-3 rounded-full bg-emerald-500"></div>
                            <span className="text-xs text-slate-600">
                              <strong>Global</strong> - All providers
                            </span>
                          </div>
                          <div className="flex items-center gap-2">
                            <div className="w-3 h-3 rounded-full bg-blue-500"></div>
                            <span className="text-xs text-slate-600">
                              <strong>Provider</strong> - One provider, all
                              contexts
                            </span>
                          </div>
                          <div className="flex items-center gap-2">
                            <div className="w-3 h-3 rounded-full bg-purple-500"></div>
                            <span className="text-xs text-slate-600">
                              <strong>Context</strong> - One resource
                            </span>
                          </div>
                          <div className="flex items-center gap-2">
                            <div className="w-3 h-3 rounded-full bg-cyan-500"></div>
                            <span className="text-xs text-slate-600">
                              <strong>Pattern</strong> - Pattern-specific (e.g.,
                              MCP)
                            </span>
                          </div>
                        </div>
                      </div>

                      {/* Concerns List */}
                      <div className="space-y-4">
                        {example.crossCuttingConcerns.map((concern) => {
                          const Icon = concern.icon;
                          const styles = getLevelStyles(concern.level);
                          return (
                            <div key={concern.id} className={styles.container}>
                              <div className="flex items-start gap-4">
                                <div className={styles.icon}>
                                  <Icon className="w-5 h-5 text-white" />
                                </div>
                                <div className="flex-1 min-w-0">
                                  <div className="flex items-center gap-2 mb-2">
                                    <h4 className={styles.title}>
                                      {concern.name}
                                    </h4>
                                    <span className={styles.badge}>
                                      {concern.level}
                                    </span>
                                  </div>
                                  <p className={styles.description}>
                                    {concern.description}
                                  </p>
                                  <div className="flex items-center gap-2">
                                    <FileCode className={styles.fileIcon} />
                                    <code className={styles.fileCode}>
                                      {concern.location}
                                    </code>
                                  </div>
                                </div>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                );
              })()}
          </div>
        </div>

        {/* Real World Examples */}
        <div className="mt-8">
          <div className="flex items-center gap-3 mb-4">
            <Lightbulb className="w-6 h-6 text-amber-600" />
            <h3 className="text-2xl font-bold text-slate-900 m-0">
              Try These Examples
            </h3>
          </div>
          <div className="grid md:grid-cols-2 gap-4">
            {realWorldExamples.map((example) => {
              const Icon = example.icon;
              const isActive = activeExample === example.id;
              return (
                <button
                  key={example.id}
                  onClick={() => loadExample(example)}
                  className={`text-left p-6 rounded-2xl border-2 transition-all duration-300 ${
                    isActive
                      ? "bg-amber-50 border-amber-500 shadow-lg scale-[1.02]"
                      : "bg-white border-slate-200 hover:border-amber-300 hover:shadow-md"
                  }`}
                >
                  <div className="flex items-start gap-4">
                    <div
                      className={`w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 ${
                        isActive ? "bg-amber-500" : "bg-amber-100"
                      }`}
                    >
                      <Icon
                        className={`w-6 h-6 ${
                          isActive ? "text-white" : "text-amber-600"
                        }`}
                      />
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="font-bold text-slate-900 mb-2 flex items-center gap-2">
                        {example.name}
                        {isActive && (
                          <span className="text-xs px-2 py-1 bg-amber-500 text-white rounded-full">
                            Active
                          </span>
                        )}
                      </div>
                      <p className="text-sm text-slate-700 mb-2">
                        {example.description}
                      </p>
                      <div className="text-xs text-slate-500 italic">
                        {example.useCase}
                      </div>
                      <div className="mt-3 flex items-center gap-2 flex-wrap">
                        <span className="text-xs px-2 py-1 bg-slate-100 rounded font-mono text-slate-700">
                          {example.pattern.toUpperCase()}
                        </span>
                        {example.presetOptions.map((opt) => {
                          const option = scaffoldOptions.find(
                            (o) => o.id === opt
                          );
                          return option ? (
                            <span
                              key={opt}
                              className="text-xs px-2 py-1 bg-emerald-100 text-emerald-700 rounded"
                            >
                              {option.label}
                            </span>
                          ) : null;
                        })}
                      </div>
                    </div>
                  </div>
                </button>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}
