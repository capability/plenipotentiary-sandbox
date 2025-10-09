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
  const [activeExampleTab, setActiveExampleTab] = useState<
    "simple" | "error" | "complete"
  >("simple");
  const [activeLaravelTab, setActiveLaravelTab] = useState<
    "controller" | "job" | "command" | "action"
  >("controller");

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
        "CampaignReadMany.php",
        "CampaignUpdate.php",
        "CampaignDelete.php",
      ],
      gatewayMethods: [
        "create($dto)",
        "read($id)",
        "readMany($filter)",
        "update($dto)",
        "delete($id)",
      ],
      repositoryNote: "Optional",
      returnType: "Result<CanonicalDTO>",
    },
    {
      id: "operation",
      title: "Operation Pattern - Use Cases",
      icon: Zap,
      color: "purple",
      description:
        "For operations beyond CRUD - search, generate, calculate, verify. Use this when the operation isn't about changing resource fields. If you need to pause a campaign (update status field), use CRUD + Laravel Actions instead. Avoids Gateway-calling-Gateway issues.",
      transport: "SDK or REST (Saloon)",
      examples: [
        "eBay Search",
        "OpenAI Completions",
        "Calculate Pricing",
        "Verify Availability",
      ],
      adapterFiles: [
        "DTO/SearchItemsDTO.php",
        "Adapter/SearchItems/SearchItemsOperation.php",
        "Adapter/CreateCompletion/CreateCompletionOperation.php",
        "Adapter/VerifyAvailability/VerifyOperation.php",
      ],
      gatewayMethods: [
        "search($dto)",
        "createCompletion($dto)",
        "verify($dto)",
      ],
      repositoryNote: "Optional/swappable",
      returnType: "Result<{UseCase}DTO>",
      highlight: "For operations that don't map to resource field changes",
    },
    {
      id: "rest",
      title: "REST Pattern - Native Saloon",
      icon: Globe,
      color: "green",
      description:
        "Clean RESTful APIs using Saloon's native Request/Response pattern. Two modes: (1) Operation-like use cases use {UseCase}DTO with Gateway for validation/policies, (2) Simple calls use pure Saloon without Gateway overhead. For CRUD operations, use the CRUD pattern instead.",
      transport: "REST (Saloon)",
      examples: [
        "OpenAI Completion",
        "Weather API",
        "GitHub API",
        "SendGrid Email",
      ],
      adapterFiles: [
        "// Mode 1: Operation-like with Gateway",
        "DTO/CreateCompletionDTO.php",
        "Adapter/RestAdapter.php",
        "// Mode 2: Pure Saloon (no Gateway)",
        "Rest/Connector.php",
        "Requests/GetWeatherRequest.php",
      ],
      gatewayMethods: [
        "// Mode 1: With Gateway + validation",
        "$gateway->execute($completionDTO)",
        "// Mode 2: Pure Saloon",
        "$connector->send(new GetWeatherRequest())",
      ],
      repositoryNote: "Flexible",
      returnType: "Result<{UseCase}DTO> OR Saloon Response",
      highlight:
        "For CRUD operations, use CRUD pattern. REST is for operations and simple calls.",
    },
    {
      id: "procedure",
      title: "Procedure Pattern - Rapid Prototyping",
      icon: Terminal,
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
      adapterFiles: ["Adapter/ProcedureAdapter.php (handles all)"],
      gatewayMethods: ["call($operation, $payload)"],
      repositoryNote: "Optional/swappable",
      returnType: "Result<mixed>",
    },
    {
      id: "mcp",
      title: "MCP Proxy Pattern - Controlled AI Agent Tool Access",
      icon: Brain,
      color: "pink",
      description:
        "Proxy MCP servers through your Laravel app to add budget tracking, rate limiting, and audit trails when AI agents (Claude, ChatGPT) need controlled access to high-stakes tools (database, email, billing).",
      transport: "HTTP API → MCP (stdio/SSE)",
      examples: [
        "Proxy Database MCP",
        "Proxy Filesystem MCP",
        "Proxy Slack MCP",
        "Proxy Email MCP",
      ],
      adapterFiles: [
        "Adapter/McpProxyAdapter.php",
        "Support/McpServerConnector.php (stdio/SSE)",
        "Http/Controllers/McpProxyController.php",
      ],
      gatewayMethods: [
        "proxyToolCall($tool, $params)",
        "forwardToMcpServer($request)",
      ],
      repositoryNote: "N/A (proxies existing MCP servers)",
      returnType: "Result<McpToolResult>",
      highlight: "Proxies existing MCP servers, doesn't create new ones",
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
            Promotes understanding vs over-abstraction - Five patterns, one
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
                supports CRUD where it fits but doesn't pretend it covers
                everything. It offers multiple integration patterns to match
                different API shapes - each built on Laravel's native tooling
                and proven libraries like Saloon for HTTP.
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
                  Your application code and the Adapter (actual API integration
                  logic). This is NOT a magic wrapper; you still implement the
                  integration, but with structure and safety guardrails.
                </p>
              </div>

              {/* Right: What Plenipotentiary Provides */}
              <div className="bg-white rounded-lg p-4 border-l-4 border-emerald-500">
                <h4 className="font-bold text-slate-900 mb-3 flex items-center gap-2">
                  <Shield className="w-5 h-5 text-emerald-600" />
                  What Plenipotentiary Provides
                </h4>
                <p className="text-base text-slate-700 leading-relaxed">
                  The Gateway layer (stable contracts, validation, policies) and
                  scaffolding commands to generate boilerplate. Consistent
                  interfaces enable robust test harnesses that work across all
                  integration; Mock the Gateway, swap adapters for test doubles,
                  verify behavior without external API calls.
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
                  need to code the integration (adapter). Plenipotentiary offers
                  a guided adapter approach that gives you structure, stability,
                  and robustness without hiding the API from you.
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
                      <p className="text-sm text-slate-600">{option.desc}</p>
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
                  Every pattern returns a consistent{" "}
                  <code className="bg-slate-200 px-1.5 py-0.5 rounded text-slate-800 font-mono text-xs">
                    Result&lt;T&gt;
                  </code>{" "}
                  interface - whether{" "}
                  <code className="bg-slate-200 px-1.5 py-0.5 rounded text-slate-800 font-mono text-xs">
                    Result&lt;CanonicalDTO&gt;
                  </code>{" "}
                  (CRUD) or{" "}
                  <code className="bg-slate-200 px-1.5 py-0.5 rounded text-slate-800 font-mono text-xs">
                    Result&lt;{"{UseCase}"}DTO&gt;
                  </code>{" "}
                  (Operation). Predictable, testable, transport-agnostic. From
                  simplest to most complex syntax:
                </p>

                {/* Tab Navigation */}
                <div className="flex gap-2 mb-4">
                  <button
                    onClick={() => setActiveExampleTab("simple")}
                    className={`px-4 py-2 rounded-lg text-sm font-semibold transition-all ${
                      activeExampleTab === "simple"
                        ? "bg-emerald-500 text-white shadow-md"
                        : "bg-white text-slate-600 hover:bg-slate-100 border border-slate-200"
                    }`}
                  >
                    1. Simple
                  </button>
                  <button
                    onClick={() => setActiveExampleTab("error")}
                    className={`px-4 py-2 rounded-lg text-sm font-semibold transition-all ${
                      activeExampleTab === "error"
                        ? "bg-blue-500 text-white shadow-md"
                        : "bg-white text-slate-600 hover:bg-slate-100 border border-slate-200"
                    }`}
                  >
                    2. Error Handling
                  </button>
                  <button
                    onClick={() => setActiveExampleTab("complete")}
                    className={`px-4 py-2 rounded-lg text-sm font-semibold transition-all ${
                      activeExampleTab === "complete"
                        ? "bg-purple-500 text-white shadow-md"
                        : "bg-white text-slate-600 hover:bg-slate-100 border border-slate-200"
                    }`}
                  >
                    3. Complete
                  </button>
                </div>

                {/* Tab Content */}
                <div className="space-y-4">
                  {/* Simple: Basic check */}
                  {activeExampleTab === "simple" && (
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
                  )}

                  {/* Medium: Error handling */}
                  {activeExampleTab === "error" && (
                    <div className="bg-white p-4 rounded-lg border border-slate-200">
                      <div className="flex items-center gap-2 mb-2">
                        <div className="w-6 h-6 rounded bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                          2
                        </div>
                        <span className="text-xs font-bold text-slate-700">
                          Error Handling with ErrorMapper
                        </span>
                      </div>
                      <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                        <code className="font-mono">{`$result = $gateway->update($dto);

if ($result->isErr()) {
    // ErrorMapper (Shared/Support) translated provider error → domain error
    // Gateway applied ErrorMapper before returning Result

    $error = $result->error();           // Consistent domain error structure
    $rawResponse = $result->rawResponse(); // Original provider error (always available)

    // Same error structure whether Google Ads, Stripe, or eBay
    return response()->json($error, 500);
}`}</code>
                      </pre>
                    </div>
                  )}

                  {/* Complex: Full validation + raw response */}
                  {activeExampleTab === "complete" && (
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
// Gateway validated INPUT_SPEC before calling adapter
// Gateway applied ErrorMapper from Shared/Support (provider-specific → domain errors)

// 1. Provider rejected our data (their validation, not ours)
if ($result->isInvalid()) {
    // ErrorMapper translated: GoogleAdsException → DomainInvalidException
    $violations = $result->violations();   // Normalized violation structure
    $rawResponse = $result->rawResponse(); // Original Google Ads error (always available)

    return response()->json([
        'message' => 'Provider rejected data',
        'violations' => $violations
    ], 422);
}

// 2. Provider error (network, API limit, auth failure, etc.)
if ($result->isErr()) {
    // ErrorMapper translated: GoogleAdsException → DomainException
    $error = $result->error();             // Consistent domain error structure
    $rawResponse = $result->rawResponse(); // Original Google Ads error (always available)

    // Same error shape whether Google Ads, Stripe, eBay, or custom APIs
    // Original provider error always accessible for debugging
    return response()->json($error, 500);
}

// 3. Success: Get canonical DTO AND raw provider response
$campaign = $result->unwrap();           // Canonical DTO (normalized across providers)
$rawResponse = $result->rawResponse();   // Provider response (Google's MutateCampaignsResponse)

Log::info('Campaign created', [
    'externalId' => $campaign->externalId,
    'resourceName' => $rawResponse->getResults()[0]->getResourceName(),
]);`}</code>
                      </pre>
                    </div>
                  )}

                  {/* What rawResponse() gives you */}
                  <div className="bg-gradient-to-r from-amber-50 to-orange-50 p-4 rounded-lg border border-amber-200">
                    <div className="flex items-start gap-2">
                      <Info className="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
                      <div className="text-sm text-slate-700 leading-relaxed">
                        <strong className="text-amber-900">
                          What is rawResponse()?
                        </strong>
                        <p className="mt-1">
                          <code className="bg-white px-1.5 py-0.5 rounded text-slate-800 font-mono">
                            unwrap()
                          </code>{" "}
                          returns your <strong>domain DTO</strong> (CanonicalDTO
                          for CRUD, {"{UseCase}"}DTO for Operation - consistent
                          across providers).
                        </p>
                        <p className="mt-1">
                          <code className="bg-white px-1.5 py-0.5 rounded text-slate-800 font-mono">
                            rawResponse()
                          </code>{" "}
                          returns the <strong>actual provider response</strong>{" "}
                          (Google's MutateCampaignsResponse, Stripe's Charge
                          object, eBay's SearchResponse, etc.).
                        </p>
                        <p className="mt-2 text-amber-900">
                          <strong>Use it for:</strong> Debugging, logging
                          provider-specific metadata, accessing fields not in
                          your domain DTO.
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
                  <p className="text-sm text-slate-600 mb-3 leading-relaxed">
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

                  {/* Laravel Pattern Tabs */}
                  <div className="flex flex-wrap gap-2 mb-4">
                    <button
                      onClick={() => setActiveLaravelTab("controller")}
                      className={`px-3 py-2 rounded-lg text-sm font-semibold transition-all ${
                        activeLaravelTab === "controller"
                          ? "bg-emerald-500 text-white shadow-md"
                          : "bg-white text-slate-600 hover:bg-slate-100 border border-slate-200"
                      }`}
                    >
                      Controller
                    </button>
                    <button
                      onClick={() => setActiveLaravelTab("job")}
                      className={`px-3 py-2 rounded-lg text-sm font-semibold transition-all ${
                        activeLaravelTab === "job"
                          ? "bg-blue-500 text-white shadow-md"
                          : "bg-white text-slate-600 hover:bg-slate-100 border border-slate-200"
                      }`}
                    >
                      Job
                    </button>
                    <button
                      onClick={() => setActiveLaravelTab("command")}
                      className={`px-3 py-2 rounded-lg text-sm font-semibold transition-all ${
                        activeLaravelTab === "command"
                          ? "bg-purple-500 text-white shadow-md"
                          : "bg-white text-slate-600 hover:bg-slate-100 border border-slate-200"
                      }`}
                    >
                      Command
                    </button>
                    <button
                      onClick={() => setActiveLaravelTab("action")}
                      className={`px-3 py-2 rounded-lg text-sm font-semibold transition-all ${
                        activeLaravelTab === "action"
                          ? "bg-slate-500 text-white shadow-md"
                          : "bg-white text-slate-600 hover:bg-slate-100 border border-slate-200"
                      }`}
                    >
                      Action
                    </button>
                  </div>

                  <div className="space-y-3">
                    {/* Controller - Simplest */}
                    {activeLaravelTab === "controller" && (
                      <div className="space-y-3">
                        <div className="bg-white p-3 rounded border border-slate-200">
                          <div className="flex items-center gap-2 mb-2">
                            <span className="text-sm font-bold text-emerald-700">
                              Option 1: Direct Gateway Usage
                            </span>
                          </div>
                          <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                            <code className="font-mono">{`public function store(Request $req, CampaignGateway $gateway) {
    $dto = CampaignCreateDTO::fromArray($req->validated());
    $result = $gateway->create($dto);

    return $result->isOk()
        ? response()->json($result->unwrap())
        : response()->json($result->error(), 400);
}`}</code>
                          </pre>
                        </div>

                        <div className="bg-white p-3 rounded border border-slate-200">
                          <div className="flex items-center gap-2 mb-2">
                            <span className="text-xs font-bold text-emerald-700">
                              Option 2: Via Action (recommended)
                            </span>
                          </div>
                          <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                            <code className="font-mono">{`public function store(Request $req, CreateCampaignAction $action) {
    $result = $action->handle($req->validated());

    return $result->isOk()
        ? response()->json($result->unwrap())
        : response()->json($result->error(), 400);
}

// The Action internally uses the Gateway:
// class CreateCampaignAction {
//     public function __construct(private CampaignGateway $gateway) {}
//     public function handle(array $data): Result {
//         $dto = CampaignCreateDTO::fromArray($data);
//         return $this->gateway->create($dto);
//     }
// }`}</code>
                          </pre>
                        </div>
                      </div>
                    )}

                    {/* Job - Medium */}
                    {activeLaravelTab === "job" && (
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
                    )}

                    {/* Command - Complex */}
                    {activeLaravelTab === "command" && (
                      <div className="bg-white p-3 rounded border border-slate-200">
                        <div className="flex items-center gap-2 mb-2">
                          <span className="text-xs font-bold text-purple-700">
                            Command (Provider Errors + Raw Response)
                          </span>
                        </div>
                        <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                          <code className="font-mono">{`class CreateCampaignCommand extends Command {
    public function handle(CampaignGateway $gateway) {
        $dto = CampaignCreateDTO::fromArray([
            'name' => $this->argument('name'),
            'budget' => $this->option('budget'),
        ]);

        $result = $gateway->create($dto);
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
                    )}

                    {/* Action */}
                    {activeLaravelTab === "action" && (
                      <div className="space-y-3">
                        <div className="bg-white p-3 rounded border border-slate-200">
                          <div className="flex items-center gap-2 mb-2">
                            <span className="text-xs font-bold text-slate-700">
                              Action Implementation
                            </span>
                          </div>
                          <pre className="text-sm text-slate-700 leading-relaxed overflow-x-auto">
                            <code className="font-mono">{`class CreateCampaignAction extends Action {
    public function __construct(
        private CampaignGateway $gateway
    ) {}

    public function handle(array $data): Result {
        $dto = CampaignCreateDTO::fromArray($data);
        return $this->gateway->create($dto);
    }
}`}</code>
                          </pre>
                        </div>

                        <div className="bg-white p-3 rounded border border-slate-200">
                          <div className="flex items-center gap-2 mb-2">
                            <span className="text-xs font-bold text-slate-700">
                              Action Usage
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
                    )}
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

        {/* 2. Five Patterns */}
        <div className="mb-10">
          <h2 className="text-2xl font-bold text-slate-900 mb-2 text-center">
            Five Patterns for Different Integration Types
          </h2>
          <p className="text-center text-slate-600 mb-6">
            Different abstraction levels for different integration types. These
            patterns help you handle <strong>heterogeneous integrations</strong>{" "}
            (SDKs, REST, SOAP) with a consistent interface.
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

                  <p
                    className="text-base text-slate-600 mb-3 leading-relaxed"
                    dangerouslySetInnerHTML={{ __html: pattern.description.replace(/\{/g, '&#123;').replace(/\}/g, '&#125;') }}
                  />

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
                        <p
                          className={`text-xs font-mono ${colors.textDark}`}
                          dangerouslySetInnerHTML={{ __html: pattern.returnType.replace(/\{/g, '&#123;').replace(/\}/g, '&#125;') }}
                        />
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
                        <p className="text-sm text-slate-600">{concern.desc}</p>
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
                    Collaboration via INPUT_SPEC
                  </h4>
                  <p className="text-base text-blue-800 leading-relaxed mb-4">
                    All adapters define{" "}
                    <code className="px-2 py-0.5 bg-blue-100 rounded text-blue-900 font-mono text-sm">
                      INPUT_SPEC
                    </code>{" "}
                    as their contract. When sharing adapters, INPUT_SPEC becomes
                    an invaluable kickstart - self documenting errors ensures
                    everyone knows exactly what fields are needed, validation
                    rules, and defaults. This is what YOUR domain needs, not
                    everything the API/SDK call supports (See step 4 in the
                    developer workflow).
                  </p>
                  <div className="bg-slate-800 rounded-lg p-4 border border-slate-700">
                    <div className="font-mono text-xs leading-relaxed">
                      <div className="text-slate-500">
                        // CampaignCreate.php
                      </div>
                      <div className="text-purple-400">
                        public const{" "}
                        <span className="text-yellow-300">INPUT_SPEC</span> = [
                      </div>
                      <div className="ml-4 text-slate-300">
                        <div>'name' =&gt; [</div>
                        <div className="ml-4">
                          'rules' =&gt; ['required', 'string', 'min:1',
                          'max:128'],
                        </div>
                        <div>],</div>
                        <div>'status' =&gt; [</div>
                        <div className="ml-4">
                          'rules' =&gt; ['nullable',
                          'in:ENABLED,PAUSED,REMOVED'],
                        </div>
                        <div>],</div>
                        <div>'budgetMicros' =&gt; [</div>
                        <div className="ml-4">
                          'rules' =&gt; ['nullable', 'numeric', 'min:0'],
                        </div>
                        <div>],</div>
                        <div>'budgetResourceName' =&gt; [</div>
                        <div className="ml-4">
                          'rules' =&gt; ['nullable', 'string'],
                        </div>
                        <div>],</div>
                        <div className="text-slate-500">
                          // customerId comes from providerContext -
                          auto-injected
                        </div>
                        <div>'providerContext.google.customerId' =&gt; [</div>
                        <div className="ml-4">
                          'rules' =&gt; ['required', 'string'],
                        </div>
                        <div className="ml-4">
                          'source' =&gt; 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID',
                        </div>
                        <div>],</div>
                      </div>
                      <div className="text-purple-400">];</div>
                      <div className="mt-3 text-slate-500 italic">
                        <div>
                          // Gateway validates automatically via INPUT_SPEC
                        </div>
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
                    Understanding the MCP Proxy Pattern: Controlled AI Tool
                    Access
                  </h4>

                  <div className="space-y-4 text-sm text-slate-700 leading-relaxed">
                    <p>
                      <strong>This is a niche pattern</strong> for when AI
                      agents (Claude, ChatGPT) need access to high-stakes tools
                      (database queries, email sending, billing operations) and
                      you need{" "}
                      <strong>
                        budget tracking, rate limiting, and complete audit
                        trails
                      </strong>
                      . Your Laravel app acts as a{" "}
                      <strong>controlled proxy</strong> between the AI agent and
                      existing MCP servers.
                    </p>

                    <div className="bg-white p-4 rounded-lg border border-pink-200">
                      <h5 className="font-bold text-slate-900 mb-2 text-sm">
                        The MCP Proxy Flow
                      </h5>
                      <div className="space-y-2 text-sm">
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">1.</span>
                          <span>
                            <strong>User asks Claude Desktop:</strong> "Find all
                            inactive customers and send re-engagement emails"
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">2.</span>
                          <span>
                            <strong>Claude analyzes</strong> and decides it
                            needs the{" "}
                            <code className="bg-pink-100 text-pink-800 px-1 py-0.5 rounded font-mono">
                              query_database
                            </code>{" "}
                            tool (configured to call YOUR Laravel API)
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">3.</span>
                          <span>
                            <strong>Claude calls YOUR Laravel endpoint:</strong>{" "}
                            POST /api/mcp/database/query_customers
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">4.</span>
                          <span>
                            <strong>Your MCP Proxy Gateway</strong> checks
                            budget ($42/$50 used), applies rate limit (85/100
                            calls), logs the request
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">5.</span>
                          <span>
                            <strong>
                              Gateway forwards to real MCP server:
                            </strong>{" "}
                            Database MCP executes query → returns 52 customers
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">6.</span>
                          <span>
                            <strong>Results return to Claude</strong> who
                            analyzes: "Found 52 inactive customers, now I'll
                            send emails"
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">7.</span>
                          <span>
                            <strong>
                              Claude calls YOUR endpoint 52 times:
                            </strong>{" "}
                            POST /api/mcp/email/send (each call tracked, logged,
                            budget checked)
                          </span>
                        </div>
                        <div className="flex items-start gap-2">
                          <span className="text-pink-600 font-bold">8.</span>
                          <span>
                            <strong>Your Gateway proxies to Email MCP →</strong>{" "}
                            Sends emails. Total cost: $8.50. Budget remaining:
                            $41.50. All calls audited.
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
                      <ul className="space-y-1.5 ml-4 list-none pl-1">
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
                        When You Need MCP Proxy
                      </h5>
                      <div className="space-y-2">
                        <div>
                          <strong className="text-pink-900">
                            Use MCP Proxy When:
                          </strong>
                          <ul className="mt-1 space-y-1.5 list-none pl-1">
                            <li className="flex items-start gap-2">
                              <span className="text-pink-600 font-bold">•</span>
                              <span>
                                AI agents need access to high-stakes tools
                                (database, billing, customer data)
                              </span>
                            </li>
                            <li className="flex items-start gap-2">
                              <span className="text-pink-600 font-bold">•</span>
                              <span>
                                You need strict budget limits to prevent runaway
                                costs
                              </span>
                            </li>
                            <li className="flex items-start gap-2">
                              <span className="text-pink-600 font-bold">•</span>
                              <span>
                                Compliance requires complete audit trails (GDPR,
                                SOC2)
                              </span>
                            </li>
                            <li className="flex items-start gap-2">
                              <span className="text-pink-600 font-bold">•</span>
                              <span>
                                Rate limiting prevents system overload or
                                provider blocking
                              </span>
                            </li>
                          </ul>
                        </div>
                        <div>
                          <strong className="text-pink-900">
                            Skip MCP Proxy When:
                          </strong>
                          <ul className="mt-1 space-y-1.5 list-none pl-1">
                            <li className="flex items-start gap-2">
                              <span className="text-pink-600 font-bold">•</span>
                              <span>
                                Tools are read-only and low-risk (documentation,
                                logs)
                              </span>
                            </li>
                            <li className="flex items-start gap-2">
                              <span className="text-pink-600 font-bold">•</span>
                              <span>
                                Claude API's built-in token tracking is
                                sufficient
                              </span>
                            </li>
                            <li className="flex items-start gap-2">
                              <span className="text-pink-600 font-bold">•</span>
                              <span>
                                You're comfortable with AI calling MCP servers
                                directly
                              </span>
                            </li>
                            <li className="flex items-start gap-2">
                              <span className="text-pink-600 font-bold">•</span>
                              <span>
                                Simple logging at the conversation level is
                                enough
                              </span>
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
                      <strong>
                        proxying them through Laravel HTTP endpoints
                      </strong>{" "}
                      to add budget tracking, rate limiting, and audit logging
                      for high-stakes AI agent workflows. This is a niche
                      pattern - most use cases can call Claude/ChatGPT APIs
                      directly (Operation/REST patterns).
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
