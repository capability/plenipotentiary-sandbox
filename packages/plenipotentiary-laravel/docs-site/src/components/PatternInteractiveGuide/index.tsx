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
    persistence: number;
    idempotency: number;
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
      persistence: 100,
      idempotency: 100,
    },
    icon: Database,
    color: "blue",
  },
  {
    id: "operation",
    name: "Operation Pattern",
    tagline: "Use Case Driven",
    when: "Non-CRUD operations like search, generate, verify, calculate",
    structure: `Contexts/Default/
  ├── Operations/
  │   └── {UseCase}/
  │       ├── {UseCase}Operation.php
  │       ├── {UseCase}Gateway.php
  │       ├── {UseCase}DTO.php
  │       └── {UseCase}Result.php
  └── Actions/
      └── {UseCase}Action.php`,
    example: `$dto = SearchItemsDTO::fromArray([
  'query' => 'laptop',
  'priceMax' => 500,
  'condition' => 'NEW',
]);

$result = $searchAction->handle($dto);`,
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
      persistence: 80,
      idempotency: 100,
    },
    icon: Zap,
    color: "emerald",
  },
  {
    id: "rest",
    name: "REST Pattern",
    tagline: "Saloon Request/Response",
    when: "Clean RESTful APIs where Saloon's native pattern is perfect",
    structure: `app/Integration/{Provider}/
  ├── {Provider}Connector.php       (Saloon Connector)
  ├── Requests/
  │   ├── CreatePaymentRequest.php  (Saloon Request)
  │   ├── GetCustomerRequest.php
  │   └── ProcessRefundRequest.php

  // Optional: Add Gateway only if you need
  // validation, policies, or persistence
  └── Gateway/
      └── {Provider}Gateway.php`,
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
      persistence: 20,
      idempotency: 60,
    },
    icon: Globe,
    color: "cyan",
  },
  {
    id: "procedure",
    name: "Procedure Pattern",
    tagline: "Simple RPC",
    when: "Quick prototypes, simple one-off operations",
    structure: `Shared/Transfer/Procedure/
  ├── {Provider}ProcedureAdapter.php
  ├── {Provider}ProcedureGateway.php
  └── {Provider}ProcedureConnector.php`,
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
      persistence: 40,
      idempotency: 40,
    },
    icon: Wrench,
    color: "orange",
  },
  {
    id: "mcp",
    name: "MCP Pattern",
    tagline: "AI Agent Tool Access",
    when: "Giving AI agents (Claude, GPT) safe, controlled access to tools via Model Context Protocol",
    structure: `Pleni/MCP/
  ├── Contexts/Default/
  │   └── Operations/CallTool/
  │       ├── CallToolOperation.php
  │       ├── CallToolGateway.php
  │       └── CallToolDTO.php
  ├── Shared/
  │   ├── Transport/McpClient.php
  │   ├── Support/McpServerRegistry.php
  │   └── Policies/
  │       ├── AgentBudgetPolicy.php
  │       └── AgentRateLimitPolicy.php`,
    example: `// Call MCP tool (filesystem, database, etc.)
$result = app(CallToolAction::class)->handle(
    server: 'filesystem',
    tool: 'read_file',
    arguments: ['path' => storage_path('logs/laravel.log')],
    agentId: 'log-analyzer'
);

// Budget tracked, rate limited, fully audited
// Perfect for AI agents calling tools`,
    useCases: [
      "Filesystem Access for AI",
      "Database Query Tools",
      "Log Analysis Agents",
      "Deterministic PHP Workflows",
    ],
    features: {
      typeSafety: 100,
      validation: 100,
      discoverability: 100,
      easeOfSetup: 70,
      persistence: 100,
      idempotency: 100,
    },
    icon: Brain,
    color: "purple",
  },
];

const FeatureBar = ({ label, value }: { label: string; value: number }) => (
  <div className="mb-3">
    <div className="flex items-center justify-between mb-1">
      <span className="text-sm font-medium text-slate-700">{label}</span>
      <span className="text-sm font-bold text-slate-900">{value}%</span>
    </div>
    <div className="h-2 bg-slate-200 rounded-full overflow-hidden">
      <div
        className={`h-full rounded-full transition-all duration-300 ${
          value >= 80
            ? "bg-gradient-to-r from-emerald-400 to-emerald-600"
            : value >= 60
              ? "bg-gradient-to-r from-amber-400 to-amber-600"
              : "bg-gradient-to-r from-red-400 to-red-600"
        }`}
        style={{ width: `${value}%` }}
      />
    </div>
  </div>
);

export default function PatternInteractiveGuide() {
  const [selectedPattern, setSelectedPattern] = useState<PatternType>("crud");
  const currentPattern = patterns.find((p) => p.id === selectedPattern) || patterns[0];

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
            <h2 className="text-3xl font-bold text-slate-900 m-0">
              Patterns
            </h2>
          </div>
          <p className="text-lg text-slate-600 max-w-3xl mx-auto">
            Five proven patterns for different integration styles. Pick the one that matches your API, not a one-size-fits-all wrapper.
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
                className={`p-6 rounded-2xl border-2 transition-all duration-300 text-left ${
                  isActive
                    ? `${colors.bgLight} ${colors.border} shadow-lg`
                    : "bg-white border-slate-200 hover:border-slate-300 hover:shadow-md"
                }`}
              >
                <div className="flex items-center gap-3 mb-3">
                  <div
                    className={`w-12 h-12 rounded-2xl flex items-center justify-center ${
                      isActive ? colors.bg : "bg-slate-100"
                    }`}
                  >
                    <Icon className={`w-6 h-6 ${isActive ? "text-white" : "text-slate-600"}`} />
                  </div>
                  <div className="font-bold text-base text-slate-900">{pattern.name}</div>
                </div>
                <div className="text-sm text-slate-600">{pattern.tagline}</div>
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
                    label="Persistence"
                    value={currentPattern.features.persistence}
                  />
                  <FeatureBar
                    label="Idempotency"
                    value={currentPattern.features.idempotency}
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
      </div>
    </div>
  );
}
