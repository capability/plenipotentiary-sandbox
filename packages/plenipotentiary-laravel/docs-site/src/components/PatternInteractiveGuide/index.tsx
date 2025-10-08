import React, { useState } from "react";
import {
  Database,
  Zap,
  Wrench,
  Brain,
  CheckCircle,
  Boxes,
  Shield,
  FileCode,
  Globe,
  Info,
  ArrowRight,
} from "lucide-react";

type PatternType = "crud" | "operation" | "rest" | "procedure" | "mcp";

interface Pattern {
  id: PatternType;
  name: string;
  tagline: string;
  when: string;
  structure: string;
  example: string;
  useCases: string[];
  features: {
    typeSafety: number;
    validation: number;
    discoverability: number;
    easeOfSetup: number;
    structureOverhead: number;
    ideSupport: number;
  };
  icon: React.ComponentType<{ size?: number; className?: string }>;
  color: string;
}

const patterns: Pattern[] = [
  {
    id: "crud",
    name: "CRUD Pattern",
    tagline: "Resource Lifecycle Management",
    when: "Managing resources with Create/Read/Update/Delete lifecycle",
    structure: `Contexts/{Context}/{Resource}/
  ├── Adapter/
  │   ├── {Resource}CrudAdapter.php
  │   ├── {Resource}Create.php
  │   ├── {Resource}Read.php
  │   ├── {Resource}ReadMany.php
  │   ├── {Resource}Update.php
  │   └── {Resource}Delete.php
  ├── Gateway/
  │   └── {Resource}CrudGateway.php
  └── DTO/
      └── {Resource}CanonicalDTO.php`,
    example: `$campaign = CampaignCanonicalDTO::fromArray([
  'name' => 'Summer Sale',
  'budget' => 50000,
  'status' => 'ENABLED',
]);

$result = $gateway->create($campaign);`,
    useCases: [
      "Google Ads Campaigns",
      "Stripe Customers",
      "Shopify Products",
      "Xero Invoices",
    ],
    features: {
      typeSafety: 100,
      validation: 100,
      discoverability: 100,
      easeOfSetup: 60,
      structureOverhead: 60,
      ideSupport: 100,
    },
    icon: Database,
    color: "blue",
  },
  {
    id: "operation",
    name: "Operation Pattern",
    tagline: "Use Case Driven",
    when: "Operations beyond CRUD that don't act on resource fields - search, generate, verify, calculate. If pausing a campaign (updating status field), use CRUD + Laravel Actions instead to avoid Gateway-calling-Gateway issues.",
    structure: `Pleni/{Provider}/{Domain}/
  ├── Contexts/Default/Operations/
  │   ├── {UseCase}/
  │   │   ├── {UseCase}Operation.php
  │   │   └── {UseCase}DTO.php
  │   └── Actions/
  │       └── {UseCase}Action.php
  │
  └── Shared/Transfer/
      ├── {Provider}{Domain}OperationGateway.php
      └── {Provider}{Domain}OperationAdapter.php`,
    example: `// Like CRUD but for non-CRUD use cases
$dto = SearchItemsDTO::fromArray([
  'query' => 'laptop',
  'priceMax' => 500,
  'condition' => 'NEW',
]);

$result = $gateway->searchItems($dto);

// Unwrap to get the same DTO, now with results
$search = $result->unwrap();  // SearchItemsDTO
$search->items;       // Results from operation
$search->totalCount;  // Total found
$search->query;       // Original input preserved`,
    useCases: [
      "eBay Browse Search",
      "OpenAI Completions",
      "Google Ads Reporting",
      "Price Calculators",
    ],
    features: {
      typeSafety: 100,
      validation: 100,
      discoverability: 100,
      easeOfSetup: 80,
      structureOverhead: 70,
      ideSupport: 100,
    },
    icon: Zap,
    color: "emerald",
  },
  {
    id: "rest",
    name: "REST Pattern",
    tagline: "Saloon Request/Response",
    when: "Clean RESTful APIs where Saloon's native pattern is perfect",
    structure: `Pleni/{Provider}/{Domain}/
  ├── Shared/Transfer/Rest/
  │   └── {Provider}{Domain}RestConnector.php
  │
  └── Contexts/Default/{Resource}/
      └── Requests/
          ├── CreatePaymentRequest.php
          ├── GetCustomerRequest.php
          └── ProcessRefundRequest.php

  Optional (if using Gateway pattern):
  ├── Shared/Transfer/Rest/
  │   ├── {Provider}{Domain}RestGateway.php
  │   └── {Provider}{Domain}RestAdapter.php`,
    example: `// Pure Saloon - use if you don't need Gateway features
$stripe = new StripeConnector($apiKey);
$response = $stripe->send(new CreatePaymentRequest(
    amount: 5000,
    currency: 'usd'
));

// With Gateway - use when you need validation/policies
$result = $gateway->createPayment(CreatePaymentDTO::fromArray([
    'amount' => 5000,
    'currency' => 'usd'
]));`,
    useCases: [
      "Stripe Payments",
      "SendGrid Emails",
      "Twilio SMS",
      "Most RESTful APIs",
    ],
    features: {
      typeSafety: 90,
      validation: 60,
      discoverability: 90,
      easeOfSetup: 95,
      structureOverhead: 30,
      ideSupport: 95,
    },
    icon: Globe,
    color: "cyan",
  },
  {
    id: "procedure",
    name: "Procedure Pattern",
    tagline: "Simple RPC",
    when: "Quick prototypes, simple one-off operations",
    structure: `Pleni/{Provider}/{Domain}/
  ├── Contexts/Default/Procedures/
  │   ├── SearchItems.php
  │   ├── SendNotification.php
  │   └── ProcessRefund.php
  │
  └── Shared/Procedure/
      ├── {Provider}{Domain}ProcedureAdapter.php
      ├── {Provider}{Domain}ProcedureGateway.php
      └── {Provider}{Domain}ProcedureConnector.php`,
    example: `$result = $gateway->call('searchItems', [
  'q' => 'laptop',
  'limit' => 50,
  'filter' => 'price:[..500]',
]);`,
    useCases: [
      "Admin Tools",
      "Quick Scripts",
      "Rapid Prototyping",
      "One-off Operations",
    ],
    features: {
      typeSafety: 40,
      validation: 40,
      discoverability: 40,
      easeOfSetup: 100,
      structureOverhead: 20,
      ideSupport: 40,
    },
    icon: Wrench,
    color: "orange",
  },
  {
    id: "mcp",
    name: "MCP Proxy Pattern",
    tagline: "Controlled AI Tool Access (Niche)",
    when: "AI agents need high-stakes tool access and you require strict budget limits, rate control, and complete audit trails",
    structure: `Pleni/MCP/Database/  (Proxy to Database MCP)
  ├── Gateway/
  │   └── DatabaseMcpProxyGateway.php
  ├── Adapter/
  │   └── DatabaseMcpAdapter.php  (Calls real MCP server)
  ├── Policies/
  │   ├── BudgetPolicy.php
  │   └── RateLimitPolicy.php
  ├── Support/
  │   └── AuditLogger.php
  └── Http/Controllers/
      └── DatabaseMcpController.php  (API endpoints)`,
    example: `// Claude Desktop calls YOUR Laravel API
// POST /api/mcp/database/query_customers

public function handle(Request $request)
{
    // Your Gateway applies cross-cutting concerns
    $result = $this->gateway->proxyTool(
        toolName: $request->input('tool'),
        params: $request->input('params')
    );

    // Budget tracked, rate limited, fully audited
    // Then proxies to real Database MCP server
    return response()->json($result);
}`,
    useCases: [
      "Database Queries (High Cost)",
      "Email Sending (Rate Limited)",
      "Billing Operations (Audit Required)",
      "Customer Data Access (GDPR)",
    ],
    features: {
      typeSafety: 100,
      validation: 100,
      discoverability: 100,
      easeOfSetup: 70,
      structureOverhead: 90,
      ideSupport: 100,
    },
    icon: Brain,
    color: "purple",
  },
];

const FeatureBar = ({ label, value }: { label: string; value: number }) => {
  const getBarColor = (val: number) => {
    if (val >= 80) return "bg-gradient-to-r from-emerald-400 to-emerald-600";
    if (val >= 40) return "bg-gradient-to-r from-amber-400 to-amber-600";
    return "bg-gradient-to-r from-slate-400 to-slate-600";
  };

  return (
    <div className="grid grid-cols-[140px_1fr] gap-3 items-center mb-2">
      <span className="text-sm font-medium text-slate-700">{label}</span>
      <div className="relative h-8 bg-slate-100 rounded-lg overflow-hidden">
        <div
          className={`h-full rounded-lg transition-all duration-300 ${getBarColor(
            value
          )}`}
          style={{ width: `${value}%` }}
        />
      </div>
    </div>
  );
};

export default function PatternInteractiveGuide() {
  const [selectedPattern, setSelectedPattern] = useState<PatternType>("crud");
  const currentPattern =
    patterns.find((p) => p.id === selectedPattern) || patterns[0];

  const getColorClasses = (color: string) => {
    const colorMap: Record<string, any> = {
      blue: {
        bg: "bg-blue-500",
        bgLight: "bg-blue-50",
        border: "border-blue-500",
        text: "text-blue-600",
      },
      emerald: {
        bg: "bg-emerald-500",
        bgLight: "bg-emerald-50",
        border: "border-emerald-500",
        text: "text-emerald-600",
      },
      orange: {
        bg: "bg-orange-500",
        bgLight: "bg-orange-50",
        border: "border-orange-500",
        text: "text-orange-600",
      },
      purple: {
        bg: "bg-purple-500",
        bgLight: "bg-purple-50",
        border: "border-purple-500",
        text: "text-purple-600",
      },
      cyan: {
        bg: "bg-cyan-500",
        bgLight: "bg-cyan-50",
        border: "border-cyan-500",
        text: "text-cyan-600",
      },
    };
    return colorMap[color] || colorMap.blue;
  };

  return (
    <div className="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-16 px-4">
      <div className="max-w-6xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center justify-center gap-3 mb-4">
            <Boxes className="w-10 h-10 text-emerald-600" />
            <h2 className="text-3xl font-bold text-slate-900 m-0">Patterns</h2>
          </div>
          <p className="text-lg text-slate-600 max-w-3xl mx-auto">
            Five proven patterns for different integration styles. Pick the one
            that matches your API, not a one-size-fits-all wrapper. These patterns help you handle <strong>heterogeneous integrations</strong> (SDKs, REST, SOAP) with a consistent interface.
          </p>
        </div>

        {/* Pattern Selection */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
          {patterns.map((pattern) => {
            const Icon = pattern.icon;
            const isActive = selectedPattern === pattern.id;
            const colors = getColorClasses(pattern.color);
            return (
              <button
                key={pattern.id}
                onClick={() => setSelectedPattern(pattern.id)}
                className={`p-6 rounded-2xl border-2 transition-all duration-300 text-left flex flex-col ${
                  isActive
                    ? `${colors.bgLight} ${colors.border} shadow-lg`
                    : "bg-white border-slate-200 hover:border-slate-300 hover:shadow-md"
                }`}
              >
                {/* Fixed height container for icon + name */}
                <div className="h-16 flex items-center gap-3 mb-3">
                  <div
                    className={`w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 ${
                      isActive ? colors.bg : "bg-slate-100"
                    }`}
                  >
                    <Icon
                      className={`w-6 h-6 ${
                        isActive ? "text-white" : "text-slate-600"
                      }`}
                    />
                  </div>
                  <div className="font-bold text-base text-slate-900 leading-tight">
                    {pattern.name}
                  </div>
                </div>
                {/* Tagline always starts here regardless of name wrapping */}
                <div className="text-sm text-slate-600 leading-snug">
                  {pattern.tagline}
                </div>
              </button>
            );
          })}
        </div>

        {/* Pattern Details */}
        <div className="bg-white rounded-2xl shadow-xl p-8 border-2 border-slate-300 relative overflow-hidden">
          <div className="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full -mr-32 -mt-32 opacity-50"></div>

          <div className="relative">
            <div className="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
              <p className="text-sm text-slate-700 m-0">
                <strong>Use when:</strong> {currentPattern.when}
              </p>
            </div>

            <div className="grid lg:grid-cols-2 gap-8">
              {/* Left Column - Structure & Example */}
              <div>
                <div className="flex items-center gap-2 mb-3">
                  <FileCode className="w-5 h-5 text-emerald-600" />
                  <h4 className="text-lg font-bold text-slate-900 m-0">
                    Structure
                  </h4>
                </div>
                <div className="bg-slate-900 rounded-2xl overflow-hidden shadow-lg mb-6">
                  <div className="flex items-center gap-2 px-5 py-3 bg-slate-800 border-b border-slate-700">
                    <div className="flex gap-2">
                      <div className="w-3 h-3 rounded-full bg-red-500"></div>
                      <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                      <div className="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                  </div>
                  <pre className="p-5 overflow-x-auto text-sm leading-relaxed m-0">
                    <code className="text-slate-300 font-mono">
                      {currentPattern.structure}
                    </code>
                  </pre>
                </div>

                <div className="flex items-center gap-2 mb-3">
                  <FileCode className="w-5 h-5 text-emerald-600" />
                  <h4 className="text-lg font-bold text-slate-900 m-0">
                    Developer Usage
                  </h4>
                </div>
                <div className="bg-slate-900 rounded-2xl overflow-hidden shadow-lg">
                  <div className="flex items-center gap-2 px-5 py-3 bg-slate-800 border-b border-slate-700">
                    <div className="flex gap-2">
                      <div className="w-3 h-3 rounded-full bg-red-500"></div>
                      <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                      <div className="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <span className="text-xs text-slate-400 ml-3 font-mono">
                      example.php
                    </span>
                  </div>
                  <pre className="p-5 overflow-x-auto text-sm leading-relaxed m-0">
                    <code className="text-slate-300 font-mono">
                      {currentPattern.example}
                    </code>
                  </pre>
                </div>
              </div>

              {/* Right Column - Features & Use Cases */}
              <div>
                <div className="flex items-center gap-2 mb-3">
                  <Shield className="w-5 h-5 text-emerald-600" />
                  <h4 className="text-lg font-bold text-slate-900 m-0">
                    Feature Coverage
                  </h4>
                </div>
                <div className="bg-slate-50 rounded-2xl p-5 mb-6 border border-slate-200">
                  {/* Table Headers */}
                  <div className="grid grid-cols-[140px_1fr] gap-3 mb-3 pb-2 border-b border-slate-300">
                    <span className="text-xs font-bold text-slate-600 uppercase tracking-wide">
                      Feature
                    </span>
                    <div className="grid grid-cols-3 text-xs font-bold text-slate-600 uppercase tracking-wide text-center">
                      <span>Low</span>
                      <span>Medium</span>
                      <span>High</span>
                    </div>
                  </div>

                  {/* Feature Rows */}
                  <FeatureBar
                    label="Type Safety"
                    value={currentPattern.features.typeSafety}
                  />
                  <FeatureBar
                    label="Validation"
                    value={currentPattern.features.validation}
                  />
                  <FeatureBar
                    label="Discoverability"
                    value={currentPattern.features.discoverability}
                  />
                  <FeatureBar
                    label="Ease of Setup"
                    value={currentPattern.features.easeOfSetup}
                  />
                  <FeatureBar
                    label="Structure Overhead"
                    value={currentPattern.features.structureOverhead}
                  />
                  <FeatureBar
                    label="IDE Support"
                    value={currentPattern.features.ideSupport}
                  />
                </div>

                <div className="flex items-center gap-2 mb-3">
                  <CheckCircle className="w-5 h-5 text-emerald-600" />
                  <h4 className="text-lg font-bold text-slate-900 m-0">
                    Real-World Examples
                  </h4>
                </div>
                <div className="space-y-2">
                  {currentPattern.useCases.map((useCase, idx) => (
                    <div
                      key={idx}
                      className="flex items-start gap-2 p-3 bg-emerald-50 rounded-lg border border-emerald-200"
                    >
                      <CheckCircle className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                      <span className="text-slate-800 font-medium">
                        {useCase}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* MCP Pattern Explanation - Only shows when MCP is selected */}
        {selectedPattern === "mcp" && (
          <div className="mt-8 bg-gradient-to-r from-purple-50 to-indigo-50 border-l-4 border-purple-500 rounded-r-2xl shadow-md p-6">
            <div className="flex items-start gap-4 mb-6">
              <Info className="w-6 h-6 text-purple-600 flex-shrink-0 mt-1" />
              <div>
                <h3 className="font-bold text-lg text-slate-900">
                  MCP Proxy: When AI Needs Controlled Tool Access
                </h3>
                <p className="text-sm text-slate-600 mt-1">
                  <strong>This is a niche pattern.</strong> Most apps can call
                  Claude/ChatGPT APIs directly and let the AI use MCP servers
                  without intervention.
                </p>
              </div>
            </div>

            <div className="space-y-6">
              {/* Direction Comparison */}
              <div className="grid md:grid-cols-2 gap-4">
                {/* Integrating WITH AI */}
                <div className="bg-white rounded-lg p-4 border border-purple-200">
                  <h4 className="font-semibold text-base text-slate-900 mb-2 flex items-center gap-2">
                    <ArrowRight className="w-5 h-5 text-blue-600" />
                    Calling Claude API (Operation Pattern)
                  </h4>
                  <p className="text-sm text-slate-600 mb-3">
                    Your app → Claude API
                  </p>
                  <div className="bg-slate-900 rounded-xl overflow-hidden shadow-lg">
                    <div className="flex items-center gap-2 px-4 py-2 bg-slate-800 border-b border-slate-700">
                      <div className="flex gap-2">
                        <div className="w-2 h-2 rounded-full bg-red-500"></div>
                        <div className="w-2 h-2 rounded-full bg-yellow-500"></div>
                        <div className="w-2 h-2 rounded-full bg-green-500"></div>
                      </div>
                      <span className="text-xs text-slate-400 ml-2 font-mono">
                        operation.php
                      </span>
                    </div>
                    <pre className="p-4 overflow-x-auto text-xs leading-relaxed m-0">
                      <code className="text-slate-300 font-mono">
                        {`// You call Claude for completions
$response = $claudeGateway->create(
  CreateCompletionDTO::fromArray([
    'model' => 'claude-3-5-sonnet',
    'messages' => [...]
  ])
);`}
                      </code>
                    </pre>
                  </div>
                </div>

                {/* AI Calling YOUR API which proxies MCP */}
                <div className="bg-white rounded-lg p-4 border border-purple-200">
                  <h4 className="font-semibold text-base text-slate-900 mb-2 flex items-center gap-2">
                    <ArrowRight className="w-5 h-5 text-purple-600" />
                    Claude Calls YOUR API (MCP Proxy Pattern)
                  </h4>
                  <p className="text-sm text-slate-600 mb-3">
                    Claude → Your Laravel API → MCP Server
                  </p>
                  <div className="bg-slate-900 rounded-xl overflow-hidden shadow-lg">
                    <div className="flex items-center gap-2 px-4 py-2 bg-slate-800 border-b border-slate-700">
                      <div className="flex gap-2">
                        <div className="w-2 h-2 rounded-full bg-red-500"></div>
                        <div className="w-2 h-2 rounded-full bg-yellow-500"></div>
                        <div className="w-2 h-2 rounded-full bg-green-500"></div>
                      </div>
                      <span className="text-xs text-slate-400 ml-2 font-mono">
                        mcp-proxy-controller.php
                      </span>
                    </div>
                    <pre className="p-4 overflow-x-auto text-xs leading-relaxed m-0">
                      <code className="text-slate-300 font-mono">
                        {`// Claude calls: POST /api/mcp/database/query
// Your endpoint with safety controls
$result = $gateway->proxyTool(
  tool: 'get_orders',
  params: $request->all()
);

// Budget, rate limit, audit applied
// Then proxies to real Database MCP`}
                      </code>
                    </pre>
                  </div>
                </div>
              </div>

              {/* Why MCP Proxy Matters */}
              <div className="bg-white rounded-lg p-4 border border-purple-200">
                <h4 className="font-semibold text-base text-slate-900 mb-3">
                  Why Proxy MCP Through Your Laravel App?
                </h4>
                <div className="grid md:grid-cols-2 gap-4 text-sm text-slate-700">
                  <div>
                    <p className="font-semibold text-red-600 mb-2">
                      ❌ AI Calling MCP Directly:
                    </p>
                    <ul className="space-y-1.5 ml-4 list-disc">
                      <li>No budget tracking across tools</li>
                      <li>No rate limiting per agent/session</li>
                      <li>No audit trail of AI actions</li>
                      <li>Can't enforce business rules</li>
                      <li>Runaway costs possible</li>
                    </ul>
                  </div>
                  <div>
                    <p className="font-semibold text-emerald-600 mb-2">
                      ✅ With MCP Proxy:
                    </p>
                    <ul className="space-y-1.5 ml-4 list-disc">
                      <li>Budget limits (max $50/day tracked)</li>
                      <li>Rate limiting (100 calls/min enforced)</li>
                      <li>Complete audit log of every tool call</li>
                      <li>Business rules applied (GDPR, permissions)</li>
                      <li>Graceful degradation on overload</li>
                    </ul>
                  </div>
                </div>
              </div>

              {/* Real Example */}
              <div className="bg-white rounded-lg p-4 border border-purple-200">
                <h4 className="font-semibold text-base text-slate-900 mb-3">
                  Real-World Example: Customer Support Agent
                </h4>
                <p className="text-sm text-slate-600 mb-3">
                  You run Claude in your Laravel app. When a user asks about
                  their order, Claude needs to check your database:
                </p>
                <ol className="text-sm text-slate-700 space-y-1.5 ml-4 list-decimal">
                  <li>
                    <strong>User asks:</strong> "What did I order last month?"
                  </li>
                  <li>
                    <strong>You send to Claude</strong> (Operation Pattern -
                    your app → Claude API)
                  </li>
                  <li>
                    <strong>Claude responds:</strong> "I need to call
                    get_customer_orders"
                  </li>
                  <li>
                    <strong>You execute tool via MCP</strong> (MCP Pattern -
                    Claude's request → YOUR tool)
                  </li>
                  <li>
                    <strong>MCP enforces policies:</strong> Budget check, rate
                    limit, permissions
                  </li>
                  <li>
                    <strong>You send result back to Claude</strong> with the
                    order data
                  </li>
                  <li>
                    <strong>Claude responds to user</strong> with the answer
                  </li>
                </ol>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
