import React, { useState } from "react";
import {
  Database,
  Zap,
  Globe,
  Brain,
  Shield,
  AlertCircle,
  RotateCcw,
  FileText,
  Clock,
  BarChart3,
  CheckCircle,
  Users,
  Layers,
  ArrowRight,
  Code2,
  FolderTree,
  Info,
  Terminal,
  Server,
  PlayCircle,
  List,
  MousePointer,
} from "lucide-react";

export default function PlenipotentiaryArchitecture() {
  const [activePattern, setActivePattern] = useState<string | null>(null);
  const [showDetails, setShowDetails] = useState<string | null>(null);

  const getColorClasses = (color: string) => {
    const colorMap = {
      blue: {
        bg: "bg-blue-500",
        bgLight: "bg-blue-50",
        bgLighter: "bg-blue-100",
        border: "border-blue-500",
        borderLight: "border-blue-200",
        text: "text-blue-600",
        textDark: "text-blue-700",
        textLight: "text-blue-300",
        bgDark: "bg-blue-900",
        shadow: "shadow-blue-500/30",
      },
      purple: {
        bg: "bg-purple-500",
        bgLight: "bg-purple-50",
        bgLighter: "bg-purple-100",
        border: "border-purple-500",
        borderLight: "border-purple-200",
        text: "text-purple-600",
        textDark: "text-purple-700",
        textLight: "text-purple-300",
        bgDark: "bg-purple-900",
        shadow: "shadow-purple-500/30",
      },
      orange: {
        bg: "bg-orange-500",
        bgLight: "bg-orange-50",
        bgLighter: "bg-orange-100",
        border: "border-orange-500",
        borderLight: "border-orange-200",
        text: "text-orange-600",
        textDark: "text-orange-700",
        textLight: "text-orange-300",
        bgDark: "bg-orange-900",
        shadow: "shadow-orange-500/30",
      },
      pink: {
        bg: "bg-pink-500",
        bgLight: "bg-pink-50",
        bgLighter: "bg-pink-100",
        border: "border-pink-500",
        borderLight: "border-pink-200",
        text: "text-pink-600",
        textDark: "text-pink-700",
        textLight: "text-pink-300",
        bgDark: "bg-pink-900",
        shadow: "shadow-pink-500/30",
      },
      green: {
        bg: "bg-green-500",
        bgLight: "bg-green-50",
        bgLighter: "bg-green-100",
        border: "border-green-500",
        borderLight: "border-green-200",
        text: "text-green-600",
        textDark: "text-green-700",
        textLight: "text-green-300",
        bgDark: "bg-green-900",
        shadow: "shadow-green-500/30",
      },
      indigo: {
        bg: "bg-indigo-500",
        bgLight: "bg-indigo-50",
        bgLighter: "bg-indigo-100",
        border: "border-indigo-500",
        borderLight: "border-indigo-200",
        text: "text-indigo-600",
        textDark: "text-indigo-700",
        textLight: "text-indigo-300",
        bgDark: "bg-indigo-900",
        shadow: "shadow-indigo-500/30",
      },
    };
    return colorMap[color] || colorMap.blue;
  };

  const useCaseOptions = [
    {
      icon: MousePointer,
      label: "Controllers",
      desc: "HTTP endpoints",
      color: "blue",
    },
    {
      icon: PlayCircle,
      label: "Actions",
      desc: "Lorisleiva Actions",
      color: "purple",
    },
    { icon: List, label: "Jobs", desc: "Queue workers", color: "green" },
    { icon: Terminal, label: "Commands", desc: "Artisan CLI", color: "orange" },
    { icon: Server, label: "Services", desc: "Business logic", color: "pink" },
    { icon: Brain, label: "AI Agents", desc: "LLM workflows", color: "indigo" },
  ];

  const patterns = [
    {
      id: "crud",
      title: "CRUD Pattern - Abstractable Resource Lifecycle",
      icon: Database,
      color: "blue",
      description:
        "Full lifecycle management with Create/Read/Update/Delete operations on resource-based APIs (campaigns, invoices, customers, products).",
      transport: "SDK or REST (Saloon)",
      examples: [
        "Google Ads Campaigns",
        "Stripe Customers",
        "Shopify Products",
      ],
      adapterFiles: [
        "CampaignCreate.php",
        "CampaignRead.php",
        "CampaignUpdate.php",
        "CampaignDelete.php",
      ],
      gatewayMethods: [
        "create($dto)",
        "read($id)",
        "update($dto)",
        "delete($id)",
      ],
      repositoryNote: "Optional",
      returnType: "Result<CanonicalDTO>",
    },
    {
      id: "operation",
      title: "Operation Pattern - RESTful Use Cases",
      icon: Zap,
      color: "purple",
      description:
        "Action/query operations organized by business use case. Built for REST APIs via Saloon, but not limited to REST. API results often aren't relational - swap to Redis, Mongo, S3, Elasticsearch, or any data store.",
      transport: "REST (Saloon)",
      examples: [
        "eBay Search",
        "OpenAI Completions",
        "Stripe Charges",
        "Custom APIs",
      ],
      adapterFiles: [
        "SearchItems/SearchItemsOperation.php",
        "CreateCompletion/CreateCompletionOperation.php",
        "VerifyAvailability/VerifyOperation.php",
      ],
      gatewayMethods: [
        "search($dto)",
        "createCompletion($dto)",
        "verify($dto)",
      ],
      repositoryNote: "Optional/swappable",
      returnType: "Result<UseCaseResult>",
      highlight: "Leverages Saloon - best-in-class HTTP client",
    },
    {
      id: "procedure",
      title: "Procedure Pattern - Rapid Prototyping",
      icon: Globe,
      color: "orange",
      description:
        "Quick prototyping with dynamic operation names for fast iteration and exploration.",
      transport: "SDK or REST (Saloon)",
      examples: [
        "Admin Tools",
        "Quick Scripts",
        "Prototyping",
        "One-off Tasks",
      ],
      adapterFiles: ["ProcedureAdapter.php (handles all)"],
      gatewayMethods: ["call($operation, $payload)"],
      repositoryNote: "Optional/swappable",
      returnType: "Result<mixed>",
    },
    {
      id: "mcp",
      title: "MCP Pattern - AI Agent Tool Access",
      icon: Brain,
      color: "pink",
      description:
        "Laravel client for consuming MCP servers - provide filesystem, database, or custom tools to AI agents (like Claude) with budget tracking, rate limiting, and complete audit trails.",
      transport: "MCP (stdio/SSE)",
      examples: [
        "Filesystem Tools",
        "Database Queries",
        "Code Analysis",
        "Multi-step Agents",
      ],
      adapterFiles: [
        "CallTool/CallToolOperation.php",
        "ReadResource/ReadResourceOperation.php",
        "ListTools/ListToolsOperation.php",
      ],
      gatewayMethods: ["callTool($dto)", "readResource($dto)", "listTools()"],
      repositoryNote: "Optional/swappable",
      returnType: "Result<ToolResult>",
      highlight: "Budget tracking, rate limits, audit logs",
    },
  ];

  const crossCuttingConcerns = [
    {
      icon: FileText,
      label: "Logging",
      desc: "Laravel Log facade",
      color: "slate",
    },
    {
      icon: RotateCcw,
      label: "Retries",
      desc: "Automatic backoff",
      color: "emerald",
    },
    { icon: Clock, label: "Idempotency", desc: "Laravel Cache", color: "blue" },
    {
      icon: AlertCircle,
      label: "Error Mapping",
      desc: "Domain exceptions",
      color: "red",
    },
    {
      icon: Shield,
      label: "Rate Limiting",
      desc: "Laravel RateLimiter",
      color: "amber",
    },
    {
      icon: BarChart3,
      label: "Observability",
      desc: "Metrics & events",
      color: "purple",
    },
  ];

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-12 px-4">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center justify-center gap-3 mb-4">
            <Layers className="w-10 h-10 text-emerald-600" />
            <h2 className="text-3xl font-bold text-slate-900 m-0">
              Architecture
            </h2>
          </div>
          <p className="text-lg text-slate-600 max-w-3xl mx-auto">
            Promotes understanding vs over abstraction - Four patterns, one
            stable platform
          </p>
        </div>

        {/* Key Insight */}
        <div className="mb-10 p-6 bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-r-xl shadow-md">
          <div className="flex gap-4">
            <Info className="w-6 h-6 text-amber-600 flex-shrink-0 mt-1" />
            <div>
              <h3 className="font-bold text-slate-900 mb-2">Core Principle</h3>
              <p className="text-slate-700 leading-relaxed">
                <strong>Abstract the Abstractable (CRUD):</strong> Pleni
                supports CRUD where it fits, but most real integrations need
                additional patterns. It offers multiple integration patterns to
                match different API shapes, each built on Laravel's native
                tooling and proven libraries like Saloon for HTTP.
              </p>
            </div>
          </div>
        </div>

        {/* Visual Flow Diagram */}
        <div className="mb-10 bg-white rounded-2xl shadow-xl p-8 border border-slate-200">
          <h3 className="text-xl font-bold text-slate-900 mb-6 text-center">
            How Plenipotentiary Works: The Flow
          </h3>

          <div className="flex flex-col md:flex-row items-center justify-between gap-6 mb-6">
            {/* Your Application */}
            <div className="flex-1 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl p-4 text-center">
              <Code2 className="w-10 h-10 text-blue-600 mx-auto mb-2" />
              <h4 className="font-bold text-slate-900 mb-1">
                Your Application
              </h4>
              <p className="text-sm text-slate-600">
                Controllers, Jobs, Commands
              </p>
              <div className="mt-2 text-sm text-slate-500 italic">
                You write this
              </div>
            </div>

            <ArrowRight className="w-6 h-6 text-slate-400 flex-shrink-0 hidden md:block" />
            <div className="md:hidden">↓</div>

            {/* Gateway (Provided) */}
            <div className="flex-1 bg-gradient-to-br from-emerald-50 to-emerald-100 border-2 border-emerald-300 rounded-xl p-4 text-center">
              <Shield className="w-10 h-10 text-emerald-600 mx-auto mb-2" />
              <h4 className="font-bold text-slate-900 mb-1">Gateway</h4>
              <p className="text-sm text-slate-600">
                Stable, consistent contracts
              </p>
              <div className="mt-2 text-sm font-bold text-emerald-700">
                ✓ Plenipotentiary provides
              </div>
            </div>

            <ArrowRight className="w-6 h-6 text-slate-400 flex-shrink-0 hidden md:block" />
            <div className="md:hidden">↓</div>

            {/* Adapter (You Write) */}
            <div className="flex-1 bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-300 rounded-xl p-4 text-center">
              <Layers className="w-10 h-10 text-purple-600 mx-auto mb-2" />
              <h4 className="font-bold text-slate-900 mb-1">Adapter</h4>
              <p className="text-sm text-slate-600">API integration logic</p>
              <div className="mt-2 text-sm text-slate-500 italic">
                You write this
              </div>
            </div>

            <ArrowRight className="w-6 h-6 text-slate-400 flex-shrink-0 hidden md:block" />
            <div className="md:hidden">↓</div>

            {/* External API */}
            <div className="flex-1 bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-300 rounded-xl p-4 text-center">
              <Globe className="w-10 h-10 text-orange-600 mx-auto mb-2" />
              <h4 className="font-bold text-slate-900 mb-1">External API</h4>
              <p className="text-sm text-slate-600">Stripe, Google, etc.</p>
              <div className="mt-2 text-sm text-slate-500 italic">
                Third-party service
              </div>
            </div>
          </div>

          <div className="bg-slate-50 border border-slate-200 rounded-lg p-6">
            <div className="grid md:grid-cols-2 gap-6">
              {/* Left: What You Write */}
              <div className="bg-white rounded-lg p-4 border-l-4 border-slate-400">
                <h4 className="font-bold text-slate-900 mb-3 flex items-center gap-2">
                  <Code2 className="w-5 h-5 text-slate-600" />
                  What You Write
                </h4>
                <p className="text-base text-slate-700 leading-relaxed">
                  Your application code and the Adapter (actual API
                  integration logic). This is NOT a magic wrapper; you still
                  implement the integration, but with structure and safety
                  guardrails.
                </p>
              </div>

              {/* Right: What Plenipotentiary Provides */}
              <div className="bg-white rounded-lg p-4 border-l-4 border-emerald-500">
                <h4 className="font-bold text-slate-900 mb-3 flex items-center gap-2">
                  <Shield className="w-5 h-5 text-emerald-600" />
                  What Plenipotentiary Provides
                </h4>
                <p className="text-base text-slate-700 leading-relaxed">
                  The Gateway layer (stable contracts, validation, policies)
                  and scaffolding commands to generate boilerplate.
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* 1. Your Application Domain */}
        <div className="mb-8">
          <div className="bg-white rounded-2xl shadow-xl p-8 border-2 border-slate-300 relative overflow-hidden">
            <div className="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full -mr-32 -mt-32 opacity-50"></div>
            <div className="relative">
              <h2 className="text-2xl font-bold text-slate-900 mb-2 flex items-center gap-3">
                <Code2 className="w-7 h-7 text-blue-600" />
                Your Application Domain
              </h2>
              <p className="text-slate-600 mb-4">
                Use any Laravel pattern you know - all return Result&lt;T&gt;
              </p>

              <div className="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
                <p className="text-base text-slate-700 leading-relaxed">
                  <strong>This is how your code will look.</strong> You still
                  need to code the integration (adapter) - this isn't magic or
                  over-abstraction. Plenipotentiary is{" "}
                  <strong>not a universal wrapper</strong>. It's a guided
                  adapter approach that gives you structure, stability, and
                  robustness without hiding the API from you.
                </p>
              </div>

              {/* Use Case Options */}
              <div className="grid grid-cols-3 gap-4 mb-6">
                {useCaseOptions.map((option) => {
                  const Icon = option.icon;
                  const colorClasses = getColorClasses(option.color);
                  return (
                    <div
                      key={option.label}
                      className={`p-4 rounded-xl border-2 ${colorClasses.borderLight} ${colorClasses.bgLight} hover:shadow-md transition-all`}
                    >
                      <Icon className={`w-6 h-6 ${colorClasses.text} mb-2`} />
                      <h4 className="font-bold text-slate-900 text-sm mb-1">
                        {option.label}
                      </h4>
                      <p className="text-xs text-slate-600">{option.desc}</p>
                    </div>
                  );
                })}
              </div>

              {/* Code Example */}
              <div className="bg-slate-50 rounded-xl p-5 border border-slate-200">
                <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
                  Consistent Result Interface Across All Patterns
                </p>
                <p className="text-base text-slate-600 mb-4 leading-relaxed">
                  Every pattern returns{" "}
                  <code className="bg-slate-200 px-1.5 py-0.5 rounded text-slate-800 font-mono text-xs">
                    Result&lt;CanonicalDTO&gt;
                  </code>{" "}
                  - consistent, predictable, testable. From simplest to most
                  complex syntax:
                </p>

                <div className="space-y-4">
                  {/* Simple: Basic check */}
                  <div className="bg-white p-4 rounded-lg border border-slate-200">
                    <div className="flex items-center gap-2 mb-2">
                      <div className="w-6 h-6 rounded bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                        1
                      </div>
                      <span className="text-xs font-bold text-slate-700">
                        Simplest: Basic Success Check
                      </span>
                    </div>
                    <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                      <code className="font-mono">{`$result = $gateway->create($dto);

if ($result->isOk()) {
    // Success! Use the canonical DTO
    $campaign = $result->unwrap();
    echo $campaign->externalId; // '12345'
}`}</code>
                    </pre>
                  </div>

                  {/* Medium: Error handling */}
                  <div className="bg-white p-4 rounded-lg border border-slate-200">
                    <div className="flex items-center gap-2 mb-2">
                      <div className="w-6 h-6 rounded bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                        2
                      </div>
                      <span className="text-xs font-bold text-slate-700">
                        Error Handling
                      </span>
                    </div>
                    <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                      <code className="font-mono">{`$result = $gateway->update($dto);

if ($result->isErr()) {
    // Provider error (network, API limit, etc.)
    $error = $result->error();
    Log::error('Update failed', $error);
    return response()->json($error, 500);
}`}</code>
                    </pre>
                  </div>

                  {/* Complex: Full validation + raw response */}
                  <div className="bg-white p-4 rounded-lg border border-slate-200">
                    <div className="flex items-center gap-2 mb-2">
                      <div className="w-6 h-6 rounded bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">
                        3
                      </div>
                      <span className="text-xs font-bold text-slate-700">
                        Complete: Provider Errors + Raw Response Access
                      </span>
                    </div>
                    <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                      <code className="font-mono">{`$result = $gateway->create($dto);
// Gateway already validated $dto against INPUT_SPEC before calling adapter
// If INPUT_SPEC failed, we wouldn't reach the adapter at all

// Provider rejected our data (Google's validation, not ours)
if ($result->isInvalid()) {
    // Google Ads rejected the campaign (budget too low, invalid name, etc.)
    $rawResponse = $result->rawResponse(); // Check Google's actual error
    return response()->json([
        'message' => 'Provider rejected data',
        'violations' => $result->violations()
    ], 422);
}

// Provider error (network, API limit, auth failure, etc.)
if ($result->isErr()) {
    return response()->json($result->error(), 500);
}

// Success: Get canonical DTO AND raw provider response
$campaign = $result->unwrap();           // Canonical DTO (consistent)
$rawResponse = $result->rawResponse();   // Provider response (for debugging)

Log::info('Campaign created', [
    'externalId' => $campaign->externalId,
    'resourceName' => $rawResponse->getResults()[0]->getResourceName(),
]);`}</code>
                    </pre>
                  </div>

                  {/* What rawResponse() gives you */}
                  <div className="bg-gradient-to-r from-amber-50 to-orange-50 p-4 rounded-lg border border-amber-200">
                    <div className="flex items-start gap-2">
                      <Info className="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
                      <div className="text-xs text-slate-700 leading-relaxed">
                        <strong className="text-amber-900">
                          What is rawResponse()?
                        </strong>
                        <p className="mt-1">
                          <code className="bg-white px-1.5 py-0.5 rounded text-slate-800 font-mono">
                            unwrap()
                          </code>{" "}
                          returns your <strong>canonical DTO</strong>{" "}
                          (consistent across all providers).
                        </p>
                        <p className="mt-1">
                          <code className="bg-white px-1.5 py-0.5 rounded text-slate-800 font-mono">
                            rawResponse()
                          </code>{" "}
                          returns the <strong>actual provider response</strong>{" "}
                          (Google's MutateCampaignsResponse, Stripe's Charge
                          object, etc.).
                        </p>
                        <p className="mt-2 text-amber-900">
                          <strong>Use it for:</strong> Debugging, logging
                          provider-specific metadata, accessing fields not in
                          your canonical DTO.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Laravel Integration Examples */}
                <div className="mt-4 pt-4 border-t border-slate-200">
                  <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
                    Consistent Interface Across All Laravel Patterns
                  </p>
                  <p className="text-xs text-slate-600 mb-3 leading-relaxed">
                    Every pattern returns{" "}
                    <code className="bg-slate-200 px-1 py-0.5 rounded font-mono">
                      Result&lt;T&gt;
                    </code>{" "}
                    with the same methods:
                    <code className="bg-slate-200 px-1 py-0.5 rounded font-mono ml-1">
                      isOk()
                    </code>
                    ,
                    <code className="bg-slate-200 px-1 py-0.5 rounded font-mono ml-1">
                      isErr()
                    </code>
                    ,
                    <code className="bg-slate-200 px-1 py-0.5 rounded font-mono ml-1">
                      isInvalid()
                    </code>
                    ,
                    <code className="bg-slate-200 px-1 py-0.5 rounded font-mono ml-1">
                      unwrap()
                    </code>
                    ,
                    <code className="bg-slate-200 px-1 py-0.5 rounded font-mono ml-1">
                      rawResponse()
                    </code>
                  </p>

                  <div className="space-y-3">
                    {/* Controller - Simplest */}
                    <div className="bg-white p-3 rounded border border-slate-200">
                      <div className="flex items-center gap-2 mb-2">
                        <span className="text-xs font-bold text-emerald-700">
                          Controller (Simplest)
                        </span>
                      </div>
                      <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                        <code className="font-mono">{`public function store(Request $req, CreateCampaignAction $action) {
    $result = $action->handle($req->validated());

    return $result->isOk()
        ? response()->json($result->unwrap())
        : response()->json($result->error(), 400);
}`}</code>
                      </pre>
                    </div>

                    {/* Job - Medium */}
                    <div className="bg-white p-3 rounded border border-slate-200">
                      <div className="flex items-center gap-2 mb-2">
                        <span className="text-xs font-bold text-blue-700">
                          Job (Error Handling)
                        </span>
                      </div>
                      <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                        <code className="font-mono">{`class SyncCampaignsJob implements ShouldQueue {
    public function handle(CampaignGateway $gateway) {
        $result = $gateway->readMany(['status' => 'ENABLED']);

        if ($result->isErr()) {
            $this->fail($result->error());
            return;
        }

        $campaigns = $result->unwrap();
        // Sync to database...
    }
}`}</code>
                      </pre>
                    </div>

                    {/* Command - Complex */}
                    <div className="bg-white p-3 rounded border border-slate-200">
                      <div className="flex items-center gap-2 mb-2">
                        <span className="text-xs font-bold text-purple-700">
                          Command (Provider Errors + Raw Response)
                        </span>
                      </div>
                      <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                        <code className="font-mono">{`class CreateCampaignCommand extends Command {
    public function handle(CampaignGateway $gateway) {
        $result = $gateway->create($this->buildDto());
        // Gateway already checked INPUT_SPEC before calling adapter

        // Provider (Google Ads) rejected our data
        if ($result->isInvalid()) {
            $this->error('Google Ads rejected the campaign:');

            // Check raw response for Google's actual error details
            $raw = $result->rawResponse();
            $googleError = $raw->getPartialFailureError();

            foreach ($result->violations() as $v) {
                $this->line("  {$v['field']}: {$v['message']}");
            }
            return 1;
        }

        // Provider error (network, auth, rate limit, etc.)
        if ($result->isErr()) {
            $this->error($result->error()['message']);
            return 1;
        }

        // Success: canonical DTO + raw response
        $campaign = $result->unwrap();
        $this->info("Created: {$campaign->externalId}");

        // Access raw Google Ads response for detailed logging
        $raw = $result->rawResponse();
        $this->comment("Resource: {$raw->getResults()[0]->getResourceName()}");

        return 0;
    }
}`}</code>
                      </pre>
                    </div>

                    {/* Action */}
                    <div className="bg-white p-3 rounded border border-slate-200">
                      <div className="flex items-center gap-2 mb-2">
                        <span className="text-xs font-bold text-slate-700">
                          Action (Lorisleiva) - Same Interface
                        </span>
                      </div>
                      <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                        <code className="font-mono">{`$result = CreateCampaignAction::run(['name' => 'Black Friday']);

// Same Result<T> interface everywhere
if ($result->isOk()) {
    $campaign = $result->unwrap();
    $rawResponse = $result->rawResponse();
}`}</code>
                      </pre>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Flow Arrow */}
        <div className="flex justify-center mb-8">
          <div className="flex flex-col items-center">
            <div className="text-sm font-semibold text-slate-600 mb-2">
              calls
            </div>
            <ArrowRight className="w-6 h-6 text-slate-400 rotate-90" />
          </div>
        </div>

        {/* 2. Four Patterns */}
        <div className="mb-10">
          <h2 className="text-2xl font-bold text-slate-900 mb-2 text-center">
            Four Gateway/Adapter Patterns
          </h2>
          <p className="text-center text-slate-600 mb-6">
            Different abstraction levels for different integration types. REST
            pattern uses Saloon natively without the Gateway layer.
          </p>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {patterns.map((pattern) => {
              const Icon = pattern.icon;
              const isActive = activePattern === pattern.id;
              const showingDetails = showDetails === pattern.id;
              const colors = getColorClasses(pattern.color);

              return (
                <div
                  key={pattern.id}
                  className={`
                    bg-white rounded-2xl p-6 border-2 cursor-pointer
                    transition-all duration-300 flex flex-col
                    ${
                      isActive
                        ? `${colors.border} shadow-xl ${colors.shadow}`
                        : "border-slate-200 hover:border-slate-300 shadow-lg"
                    }
                  `}
                  onMouseEnter={() => setActivePattern(pattern.id)}
                  onMouseLeave={() => setActivePattern(null)}
                >
                  {/* Header */}
                  <div className="flex items-start gap-4 mb-4">
                    <div
                      className={`w-14 h-14 rounded-xl flex items-center justify-center ${colors.bg} shadow-lg flex-shrink-0`}
                    >
                      <Icon className="w-7 h-7 text-white" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <h4 className="text-lg font-bold text-slate-900 mb-2 leading-tight">
                        {pattern.title}
                      </h4>
                      <p
                        className={`text-xs px-2 py-1 ${colors.bgLighter} ${colors.textDark} rounded inline-block font-mono`}
                      >
                        {pattern.transport}
                      </p>
                    </div>
                  </div>

                  <p className="text-base text-slate-600 mb-3 leading-relaxed">
                    {pattern.description}
                  </p>

                  {/* Spacer */}
                  <div className="flex-grow"></div>

                  {/* Bottom Content */}
                  <div>
                    {/* Highlight */}
                    {pattern.highlight && (
                      <div
                        className={`mb-4 p-3 ${colors.bgLight} border-l-4 ${colors.border} rounded-r-lg`}
                      >
                        <p className="text-xs font-semibold text-slate-700">
                          {pattern.highlight}
                        </p>
                      </div>
                    )}

                    {/* Examples */}
                    <div className="mb-4 p-3 bg-slate-50 rounded-lg">
                      <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                        Use Cases:
                      </p>
                      <div className="grid grid-cols-2 gap-2">
                        {pattern.examples.map((ex, i) => (
                          <div key={i} className="flex items-center gap-2">
                            <div
                              className={`w-1.5 h-1.5 rounded-full ${colors.bg}`}
                            ></div>
                            <span className="text-xs text-slate-700">{ex}</span>
                          </div>
                        ))}
                      </div>
                    </div>

                    {/* Key Info */}
                    <div className="grid grid-cols-2 gap-3 mb-4">
                      <div
                        className={`p-3 ${colors.bgLight} rounded-lg border ${colors.borderLight}`}
                      >
                        <p className="text-xs font-semibold text-slate-600 mb-1">
                          Returns
                        </p>
                        <p className={`text-xs font-mono ${colors.textDark}`}>
                          {pattern.returnType}
                        </p>
                      </div>
                      <div
                        className={`p-3 ${colors.bgLight} rounded-lg border ${colors.borderLight}`}
                      >
                        <p className="text-xs font-semibold text-slate-600 mb-1">
                          Repository
                        </p>
                        <p className="text-xs text-slate-700 leading-relaxed">
                          ✓ {pattern.repositoryNote}
                        </p>
                      </div>
                    </div>

                    {/* Adapter Structure Toggle */}
                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        setShowDetails(showingDetails ? null : pattern.id);
                      }}
                      className={`w-full p-3 rounded-lg border-2 transition-all text-left flex items-center justify-between bg-white ${
                        showingDetails
                          ? `${colors.border} ${colors.bgLight}`
                          : "border-slate-200 hover:border-slate-300"
                      }`}
                    >
                      <div className="flex items-center gap-2">
                        <FolderTree
                          className={`w-4 h-4 ${
                            showingDetails ? colors.text : "text-slate-600"
                          }`}
                        />
                        <span className="text-sm font-semibold text-slate-700">
                          {showingDetails ? "Hide" : "Show"} Adapter Structure
                        </span>
                      </div>
                      <ArrowRight
                        className={`w-4 h-4 text-slate-400 transition-transform ${
                          showingDetails ? "rotate-90" : ""
                        }`}
                      />
                    </button>

                    {/* Adapter Details */}
                    {showingDetails && (
                      <div className="mt-4 p-4 bg-slate-900 rounded-xl">
                        <p className="text-xs font-semibold text-slate-400 mb-3 uppercase tracking-wide">
                          Adapter Files:
                        </p>
                        <div className="space-y-2 mb-4">
                          {pattern.adapterFiles.map((file, i) => (
                            <div
                              key={i}
                              className="flex items-center gap-2 text-xs font-mono text-emerald-400"
                            >
                              <span className="text-slate-500">├─</span>
                              {file}
                            </div>
                          ))}
                        </div>
                        <p className="text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">
                          Gateway Methods:
                        </p>
                        <div className="space-y-1">
                          {pattern.gatewayMethods.map((method, i) => (
                            <code
                              key={i}
                              className={`block px-2 py-1 ${colors.bgDark} ${colors.textLight} rounded text-xs font-mono`}
                            >
                              {method}
                            </code>
                          ))}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Flow Arrow */}
        <div className="flex justify-center mb-8">
          <div className="flex flex-col items-center">
            <div className="text-sm font-semibold text-slate-600 mb-2">
              applies
            </div>
            <ArrowRight className="w-6 h-6 text-slate-400 rotate-90" />
          </div>
        </div>

        {/* 3. Gateway Layer */}
        <div className="mb-8">
          <div className="bg-gradient-to-r from-emerald-50 via-blue-50 to-emerald-50 rounded-2xl p-8 border-2 border-emerald-400 shadow-xl">
            <div className="flex items-center gap-3 mb-4">
              <Shield className="w-8 h-8 text-emerald-600" />
              <div>
                <h2 className="text-2xl font-bold text-slate-900">
                  Gateway Layer: Your Stable Platform
                </h2>
                <p className="text-slate-600">
                  The stable boundary that provides robustness
                </p>
              </div>
            </div>

            <div className="mb-6 p-4 bg-white rounded-xl border border-emerald-200">
              <p className="text-base text-slate-700 leading-relaxed">
                <strong>Gateway = Stable platform.</strong> Your application
                calls the Gateway, never the vendor API directly. When provider
                APIs change, only the Adapter changes - your Gateway stays
                stable. All gateways automatically apply cross-cutting concerns
                through Laravel's native tools.
              </p>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
              {crossCuttingConcerns.map((concern) => {
                const Icon = concern.icon;
                const colorMap = {
                  slate: { bg: "bg-slate-100", text: "text-slate-600" },
                  emerald: { bg: "bg-emerald-100", text: "text-emerald-600" },
                  blue: { bg: "bg-blue-100", text: "text-blue-600" },
                  red: { bg: "bg-red-100", text: "text-red-600" },
                  amber: { bg: "bg-amber-100", text: "text-amber-600" },
                  purple: { bg: "bg-purple-100", text: "text-purple-600" },
                };
                const colors = colorMap[concern.color];

                return (
                  <div
                    key={concern.label}
                    className="bg-white rounded-xl p-5 border-2 border-slate-200 hover:border-emerald-300 hover:shadow-lg transition-all"
                  >
                    <div className="flex items-start gap-3 mb-3">
                      <div
                        className={`w-10 h-10 rounded-lg flex items-center justify-center ${colors.bg}`}
                      >
                        <Icon className={`w-5 h-5 ${colors.text}`} />
                      </div>
                      <div>
                        <h4 className="font-bold text-slate-900 text-sm mb-1">
                          {concern.label}
                        </h4>
                        <p className="text-xs text-slate-600">{concern.desc}</p>
                      </div>
                    </div>
                    <div className="text-xs text-slate-500 font-mono bg-slate-50 px-2 py-1 rounded">
                      Via GatewayPolicy
                    </div>
                  </div>
                );
              })}
            </div>

            <div className="mt-6 p-5 bg-blue-50 rounded-xl border border-blue-200">
              <div className="flex items-start gap-3">
                <Users className="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" />
                <div>
                  <h4 className="font-bold text-blue-900 mb-2">
                    Team Collaboration via INPUT_SPEC
                  </h4>
                  <p className="text-base text-blue-800 leading-relaxed mb-4">
                    All adapters define{" "}
                    <code className="px-2 py-0.5 bg-blue-100 rounded text-blue-900 font-mono text-sm">
                      INPUT_SPEC
                    </code>{" "}
                    as their contract. When teams share adapters, INPUT_SPEC
                    becomes an invaluable kickstart - everyone knows exactly
                    what fields are needed, validation rules, and defaults.
                  </p>
                  <div className="bg-slate-800 rounded-lg p-4 border border-slate-700">
                    <div className="font-mono text-sm">
                      <div className="text-purple-400">public const <span className="text-yellow-300">INPUT_SPEC</span> = [</div>
                      <div className="ml-4 text-slate-300">
                        <div>'query' =&gt; ['rules' =&gt; ['required', 'string', 'min:2']],</div>
                        <div>'limit' =&gt; ['rules' =&gt; ['integer', 'max:200'], 'default' =&gt; 50],</div>
                        <div>'priceMax' =&gt; ['rules' =&gt; ['numeric']],</div>
                      </div>
                      <div className="text-purple-400">];</div>
                      <div className="mt-3 text-slate-500 italic">
                        <div>// Gateway validates automatically via INPUT_SPEC</div>
                        <div>// Teams immediately understand the contract</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="mt-6 p-5 bg-pink-50 rounded-xl border border-pink-200">
              <div className="flex items-start gap-3">
                <Brain className="w-6 h-6 text-pink-600 flex-shrink-0 mt-1" />
                <div>
                  <h4 className="font-bold text-pink-900 mb-3">
                    Understanding the MCP Pattern: AI Agents with Safe Tool
                    Access
                  </h4>

                  <div className="space-y-4 text-sm text-slate-700 leading-relaxed">
                    <p>
                      The MCP pattern is fundamentally different from the other
                      four patterns. You're not integrating with a traditional
                      API—you're building{" "}
                      <strong>
                        infrastructure to give AI agents safe, controlled access
                        to tools
                      </strong>{" "}
                      via Anthropic's Model Context Protocol.
                    </p>

                    <div className="bg-white p-4 rounded-lg border border-pink-200">
                      <h5 className="font-bold text-slate-900 mb-2 text-sm">
                        The Complete Flow
                      </h5>
                      <div className="space-y-2 text-xs">
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">1.</span>
                          <span>
                            <strong>User asks:</strong> "Find all inactive
                            customers and send re-engagement emails"
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">2.</span>
                          <span>
                            <strong>Your Laravel app</strong> sends this to
                            Claude API with available MCP tools listed
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">3.</span>
                          <span>
                            <strong>Claude thinks:</strong> "I need to query the
                            database" and requests{" "}
                            <code className="bg-pink-100 text-pink-800 px-1 py-0.5 rounded font-mono">
                              query_database
                            </code>{" "}
                            tool
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">4.</span>
                          <span>
                            <strong>Your MCP Gateway executes</strong> the query
                            via MCP server (budget/rate limit policies run here)
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">5.</span>
                          <span>
                            <strong>Results go back to Claude</strong> who
                            analyzes: "Found 52 inactive customers"
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">6.</span>
                          <span>
                            <strong>Claude requests</strong> 52{" "}
                            <code className="bg-pink-100 text-pink-800 px-1 py-0.5 rounded font-mono">
                              send_email
                            </code>{" "}
                            tool calls
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">7.</span>
                          <span>
                            <strong>Your MCP Gateway executes</strong> each
                            email via Mailchimp MCP server (budget tracking:
                            $0.53 spent, $49.47 remaining)
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">8.</span>
                          <span>
                            <strong>Claude reports:</strong> "Sent 52
                            re-engagement emails to inactive customers"
                          </span>
                        </div>
                      </div>
                    </div>

                    <div>
                      <h5 className="font-bold text-slate-900 mb-2">
                        Why Safety Features Are Critical
                      </h5>
                      <p className="mb-2">
                        AI agents can make dozens or hundreds of tool calls
                        autonomously. Without guardrails:
                      </p>
                      <ul className="space-y-1.5 ml-4 list-none">
                        <li className="flex items-start gap-2">
                          <span className="text-red-600 font-bold">•</span>
                          <span>
                            Claude queries your database 10,000 times → $100 in
                            costs before you notice
                          </span>
                        </li>
                        <li className="flex items-start gap-2">
                          <span className="text-red-600 font-bold">•</span>
                          <span>
                            Runaway loop sends 50,000 emails in 10 seconds →
                            provider blocks you
                          </span>
                        </li>
                        <li className="flex items-start gap-2">
                          <span className="text-red-600 font-bold">•</span>
                          <span>
                            No audit trail → can't debug what went wrong or
                            replay the session
                          </span>
                        </li>
                      </ul>
                      <p className="mt-2">
                        Your MCP Gateway with <strong>budget policies</strong>{" "}
                        (max $50/day), <strong>rate limits</strong> (max 100
                        calls/minute), and <strong>complete audit logs</strong>{" "}
                        prevents all of this.
                      </p>
                    </div>

                    <div className="bg-gradient-to-r from-pink-100 to-purple-100 p-4 rounded-lg border border-pink-300">
                      <h5 className="font-bold text-pink-900 mb-2">
                        Current State vs. Future Vision
                      </h5>
                      <div className="space-y-2">
                        <div>
                          <strong className="text-pink-900">
                            What's Built Now:
                          </strong>
                          <ul className="ml-4 mt-1 space-y-1 list-none">
                            <li>
                              • MCP Gateway with budget tracking & rate limiting
                            </li>
                            <li>
                              • Tool execution via MCP servers (filesystem,
                              database, etc.)
                            </li>
                            <li>• Complete audit trail of every tool call</li>
                            <li>
                              • Can be used for deterministic PHP workflows or
                              external tool access
                            </li>
                          </ul>
                        </div>
                        <div>
                          <strong className="text-pink-900">
                            Coming Soon (AI Orchestrator):
                          </strong>
                          <ul className="ml-4 mt-1 space-y-1 list-none">
                            <li>
                              • Anthropic SDK integration (Claude + your MCP
                              tools)
                            </li>
                            <li>
                              • OpenAI function calling integration (GPT + your
                              MCP tools)
                            </li>
                            <li>• Multi-turn conversation handling</li>
                            <li>
                              • Tool registry for defining which agents can use
                              which tools
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    <p className="bg-white p-3 rounded border border-pink-200 mb-0">
                      <strong className="text-pink-900">
                        Key Distinction:
                      </strong>{" "}
                      You're <strong>not building MCP servers</strong> (those
                      already exist: @modelcontextprotocol/server-filesystem,
                      server-slack, etc.). You're{" "}
                      <strong>consuming them from Laravel</strong> with the same
                      Gateway/Adapter consistency as your other integrations,
                      but adding AI-agent-specific safety policies that prevent
                      runaway costs and system overload.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
