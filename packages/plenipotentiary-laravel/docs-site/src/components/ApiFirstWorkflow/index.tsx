import React, { useState } from "react";
import {
  Search,
  FileText,
  CheckSquare,
  Package,
  FileCode,
  Shield,
  CheckCircle,
  XCircle,
  ArrowRight,
  ArrowLeft,
  Workflow,
  Sparkles,
} from "lucide-react";

type WorkflowPhase =
  | "understand"
  | "codify"
  | "test"
  | "gateway"
  | "scaffold"
  | "robust";

interface Step {
  id: WorkflowPhase;
  number: number;
  title: string;
  principle: string;
  description: React.ReactNode;
  code: string;
  outcome: string;
  antipattern: string;
  icon: React.ComponentType<{ size?: number; className?: string }>;
  color: string;
}

const workflowSteps: Step[] = [
  {
    id: "understand",
    number: 1,
    title: "Start with the Real API",
    principle: "Learn the provider SDK/API first. No premature abstraction.",
    description: (
      <>
        Promote Understanding vs Over Abstraction. Open{" "}
        <strong>one file, one operation</strong>. Copy the provider's SDK
        example almost verbatim. Make a real API call. See what comes back.{" "}
        <em>Understand</em> before you abstract.
      </>
    ),
    code: `// CampaignCreate.php - Start here, all in one place
// Based on Google's AddCampaigns.php example (see CreateCampaignExample.php in repo root)

public function performWithArray(array $input): Result
{
    // Development helper - takes raw array, explores the API
    // Google SDK example: lines 126-149 from AddCampaigns.php

    $campaign = new Campaign([
        'name' => $input['name'],
        'advertising_channel_type' => AdvertisingChannelType::SEARCH,
        'status' => CampaignStatus::PAUSED, // Start paused
        'manual_cpc' => new ManualCpc(),
        'campaign_budget' => $input['budgetResourceName'], // From budget creation
    ]);

    $operation = new CampaignOperation();
    $operation->setCreate($campaign);

    // Google SDK: lines 153-156 from AddCampaigns.php
    $response = $this->client
        ->getCampaignServiceClient()
        ->mutateCampaigns(
            MutateCampaignsRequest::build(
                $input['customerId'],
                [$operation]
            )
        );

    // See what comes back - understand the structure!
    return Result::ok(['resourceName' => $response->getResults()[0]->getResourceName()]);
}`,
    outcome: "You understand the API call flow",
    antipattern: "Starting with abstractions before understanding the API",
    icon: Search,
    color: "emerald",
  },
  {
    id: "codify",
    number: 2,
    title: "Define Your INPUT_SPEC",
    principle: "Codify the minimum data you need. This is YOUR contract.",
    description: (
      <>
        The Explicit Contract. Identify what fields you{" "}
        <strong>actually need</strong> from your business use case. Not
        everything the API supports, just what <em>you</em> need. Write it as{" "}
        <code>INPUT_SPEC</code>. It's not replicating the API docs, it's your
        domains contact and it promotes sharing adapters.
      </>
    ),
    code: `// CampaignCreate.php
// YOUR contract - what YOUR domain needs, not everything Google supports
public const INPUT_SPEC = [
    'name' => [
        'rules' => ['required', 'string', 'min:1', 'max:128'],
    ],
    'status' => [
        'rules' => ['nullable', 'in:ENABLED,PAUSED,REMOVED'],
    ],
    'budgetMicros' => [
        'rules' => ['nullable', 'numeric', 'min:0'],
    ],
    'budgetResourceName' => [
        'rules' => ['nullable', 'string'],
    ],
    // customerId comes from providerContext - auto-injected from env
    'providerContext.google.customerId' => [
        'rules' => ['required', 'string'],
        'source' => 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID',
    ],
];

public static function inputSpec(): array
{
    return self::INPUT_SPEC;
}`,
    outcome: "Explicit, auditable contract visible in code",
    antipattern:
      'Hidden validation, magic field mappings, "the framework handles it"',
    icon: FileText,
    color: "blue",
  },
  {
    id: "test",
    number: 3,
    title: "Test Until Green",
    principle: "Write requestMapper() and responseMapper() until tests pass.",
    description: (
      <>
        Stay in One Place. Keep everything in the operation file. Build the
        request from a mock flat array. Map the response back to your domain.
        Test <strong>success, validation, and errors</strong>. Don't move on
        until it's green.
      </>
    ),
    code: `// CampaignCreate.php
private function requestMapper(array $validated): Campaign
{
    return new Campaign([
        'name' => $validated['name'],
        'advertisingChannelType' => AdvertisingChannelType::SEARCH,
        'status' => CampaignStatus::value($validated['status']),
        'campaignBudget' => $this->budgetResourceName(
            $validated['budget']
        ),
    ]);
}

private function responseMapper(MutateCampaignsResponse $res): array
{
    $result = $res->getResults()[0];
    return [
        'id' => basename($result->getResourceName()),
        'resourceName' => $result->getResourceName(),
    ];
}

// CampaignCreateTest.php - MUST be green!
test('creates campaign with valid input', function() {
    $result = $operation->perform([
        'name' => 'Test Campaign',
        'budget' => 50000,
    ]);
    
    expect($result->isOk())->toBeTrue();
});`,
    outcome: "Operation tested, API understood, confidence gained",
    antipattern: "Moving to gateway before understanding the operation",
    icon: CheckSquare,
    color: "green",
  },
  {
    id: "gateway",
    number: 4,
    title: "Call Through Gateway",
    principle: "Gateway reveals the DTO contract based on YOUR INPUT_SPEC.",
    description: (
      <>
        A table Boundary Emerges. Run through the gateway for the first time. It
        will <strong>fail intentionally</strong> and show you what DTO and
        Factory to create - derived from <em>your</em> INPUT_SPEC, not magic.
      </>
    ),
    code: `// From your Action/Controller/Job
$dto = CampaignCanonicalDTO::fromArray([]);
$result = $gateway->create($dto);

// Remote API rejected the request
// - name (required): Required
// - providerContext.google.customerId (required): Required
//
// Expected DTO shape:
{
    "dto": {
        "fields": {
            "name": {
                "required": true,
                "rules": ["required", "string", "min:1", "max:128"],
                "type": "string"
            },
            "status": {
                "required": false,
                "rules": ["nullable", "in:ENABLED,PAUSED,REMOVED"],
                "type": "enum"
            },
            "budgetMicros": {
                "required": false,
                "rules": ["nullable", "numeric", "min:0"],
                "type": "numeric"
            },
            "budgetResourceName": {
                "required": false,
                "rules": ["nullable", "string"],
                "type": "string"
            }
        },
        "providerContext": {
            "google.customerId": {
                "required": true,
                "rules": ["required", "string"],
                "source": "env:GOOGLE_ADS_LINKED_CUSTOMER_ID",
                "type": "string"
            }
        }
    }
}

// Gateway normalizes everything to Result
interface Result {
    isOk(): bool;
    isErr(): bool;
    value(): mixed;  // Your domain data
    error(): array;  // Normalized errors
}`,
    outcome: "Gateway boundary defined, contract visible",
    antipattern: "Premature DTO design before knowing what the API needs",
    icon: Package,
    color: "purple",
  },
  {
    id: "scaffold",
    number: 5,
    title: "Scaffold to Your Spec",
    principle: "Generate DTO/Factory from the INPUT_SPEC you wrote.",
    description: (
      <>
        Tooling Follows Understanding. Copy/generate the DTO and Factory shown
        in the gateway error. They're not guesses - they're{" "}
        <strong>derived from your INPUT_SPEC</strong>. The spec you wrote while
        working with the real API.
      </>
    ),
    code: `// CampaignCanonicalDTO.php - Generated from YOUR spec
final class CampaignCanonicalDTO implements CanonicalDTOContract
{
    /** @var array<string,string> */
    public array $providerContext = [];

    public ?string $internalId = null;

    public ?string $externalId = null;

    public ?string $name = null;

    public ?string $status = null;

    public ?string $budgetResourceName = null;

    public ?int $cpcBidMicros = null;

    public ?int $budgetMicros = null;

    public static function fromArray(array $data): self
    {
        $dto = new self;
        $dto->providerContext = self::filterContext($data['providerContext'] ?? $data['accountKeys'] ?? []);
        $dto->internalId = $data['internalId'] ?? null;
        $dto->externalId = $data['externalId'] ?? null;
        $dto->name = $data['name'] ?? null;
        $dto->status = $data['status'] ?? null;
        $dto->budgetResourceName = $data['budgetResourceName'] ?? null;
        $dto->cpcBidMicros = isset($data['cpcBidMicros']) ? (int) $data['cpcBidMicros'] : null;
        $dto->budgetMicros = isset($data['budgetMicros']) ? (int) $data['budgetMicros'] : null;

        return $dto;
    }
}

// CampaignCanonicalFactory.php
final class CampaignCanonicalFactory
{
    public function make(array $input): CampaignCanonicalDTO
    {
        // Uses INPUT_SPEC for validation
        return CampaignCanonicalDTO::fromArray($input);
    }
}`,
    outcome: "Type-safe contracts scaffolded from your understanding",
    antipattern: "Auto-generated DTOs that include fields you don't need",
    icon: FileCode,
    color: "orange",
  },
  {
    id: "robust",
    number: 6,
    title: "Robustness Comes Online",
    principle: "Cross-cutting concerns layer on top automatically.",
    description: (
      <>
        Progressive Enhancement. With the Gateway boundary in place, you{" "}
        <strong>automatically</strong> get robustness features. They weren't
        there when you were learning the API. They appear when you need them.
      </>
    ),
    code: `// Result is consistent: always Result<CanonicalDTO>
$result = $gateway->create($dto);

// Validation (from INPUT_SPEC)
if ($result->isInvalid()) {
    return $result->violations(); // ['budget' => 'must be >= 1000']
}

// Error Mapping (provider → domain)
if ($result->isErr()) {
    return $result->error(); // Normalized structure
}

// Success: Get the canonical DTO
if ($result->isOk()) {
    $campaign = $result->unwrap(); // CampaignCanonicalDTO
    $campaign->externalId; // '12345'
    $campaign->name; // 'My Campaign'

    // ALSO: Access raw provider response for debugging/logging
    $rawResponse = $result->rawResponse(); // MutateCampaignsResponse
    $rawResponse->getResults()[0]->getResourceName();
}

// Idempotency (automatic)
$result = $gateway->create($dto,
    idempotencyKey: 'campaign-' . $dto->name
);

// Queueing (Laravel integration)
dispatch(new CreateCampaignJob($dto));

// Observability (automatic)
// All calls logged, metrics tracked, errors mapped`,
    outcome: "Production-ready with idempotency, validation, queueing, logging",
    antipattern: "Adding cross-cutting concerns while still learning the API",
    icon: Shield,
    color: "red",
  },
];

export default function APIWorkflow() {
  const [currentStep, setCurrentStep] = useState<WorkflowPhase>("understand");
  const [completedSteps, setCompletedSteps] = useState<Set<WorkflowPhase>>(
    new Set()
  );

  const step =
    workflowSteps.find((s) => s.id === currentStep) || workflowSteps[0];
  const stepIndex = workflowSteps.findIndex((s) => s.id === currentStep);

  const handleStepComplete = () => {
    setCompletedSteps((prev) => new Set(prev).add(currentStep));
    if (stepIndex < workflowSteps.length - 1) {
      setCurrentStep(workflowSteps[stepIndex + 1].id);
    }
  };

  const handleStepSelect = (stepId: WorkflowPhase) => {
    setCurrentStep(stepId);
  };

  const allStepsCompleted = completedSteps.size === workflowSteps.length;

  return (
    <div className="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-16 px-4">
      <div className="max-w-6xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center justify-center gap-3 mb-4">
            <Workflow className="w-10 h-10 text-emerald-600" />
            <h2 className="text-3xl font-bold text-slate-900 m-0">
              Developer Workflow - CRUD SDK Example
            </h2>
          </div>
          <p className="text-lg text-slate-600 max-w-3xl mx-auto">
            Learn the real API first. Codify your understanding. Let tooling
            follow your spec.
          </p>
        </div>

        {/* Progress Steps */}
        <div className="mb-14 px-2">
          <div className="flex items-center justify-center gap-1 sm:gap-2 relative overflow-x-auto pb-2 pt-2">
            {workflowSteps.map((s, index) => {
              const Icon = s.icon;
              const isActive = s.id === currentStep;
              const isCompleted = completedSteps.has(s.id);

              return (
                <React.Fragment key={s.id}>
                  <div className="flex flex-col items-center flex-shrink-0">
                    <button
                      onClick={() => handleStepSelect(s.id)}
                      className={`
                        w-12 h-12 sm:w-16 sm:h-16 rounded-full flex items-center justify-center
                        transition-all duration-300 relative group cursor-pointer
                        ${
                          isActive
                            ? "bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-xl shadow-emerald-500/50 scale-110"
                            : ""
                        }
                        ${
                          isCompleted && !isActive
                            ? "bg-gradient-to-br from-emerald-400 to-emerald-600"
                            : ""
                        }
                        ${
                          !isActive && !isCompleted
                            ? "bg-white border-2 border-slate-300"
                            : ""
                        }
                        hover:scale-105
                      `}
                      title={s.title}
                    >
                      <Icon
                        className={`w-5 h-5 sm:w-7 sm:h-7 ${
                          isActive || isCompleted
                            ? "text-white"
                            : "text-slate-400"
                        }`}
                      />
                    </button>
                    <span
                      className={`text-xs mt-2 font-medium transition-colors ${
                        isActive ? "text-emerald-600" : "text-slate-500"
                      }`}
                    >
                      Step {s.number}
                    </span>
                  </div>
                  {index < workflowSteps.length - 1 && (
                    <div
                      className={`w-8 sm:w-16 h-1 rounded-full transition-all duration-300 flex-shrink-0 ${
                        isCompleted
                          ? "bg-gradient-to-r from-emerald-400 to-emerald-600"
                          : "bg-slate-300"
                      }`}
                    ></div>
                  )}
                </React.Fragment>
              );
            })}
          </div>
        </div>

        {/* Main Content Card */}
        <div className="bg-white rounded-2xl shadow-xl overflow-hidden mb-6 border border-slate-200">
          {/* Step Header */}
          <div className="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 px-6 py-4 text-white relative overflow-hidden">
            <div className="relative">
              <div className="flex flex-row items-center justify-between mb-3">
                <h2 className="text-2xl font-bold m-0">{step.title}</h2>
                <span className="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold whitespace-nowrap">
                  Step {step.number} of 6
                </span>
              </div>
              <p className="text-emerald-50 text-lg m-0">
                <span className="font-bold">Principle:</span> {step.principle}
              </p>
            </div>
          </div>

          {/* Content */}
          <div className="p-8">
            {/* Instructions */}
            <div className="mb-8 text-slate-700 text-lg leading-relaxed">
              {step.description}
            </div>

            {/* Outcome & Antipattern Cards */}
            <div className="grid md:grid-cols-2 gap-4 mb-6">
              <div className="rounded-lg border-2 border-emerald-300 bg-gradient-to-br from-emerald-50 to-white shadow px-4 pt-4 pb-3">
                <div className="flex items-start gap-2">
                  <div className="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center mt-0.5">
                    <CheckCircle className="w-4 h-4 text-emerald-600" />
                  </div>
                  <div>
                    <h3 className="text-sm font-bold text-emerald-700 uppercase tracking-wide m-0 mb-2">
                      Our Approach
                    </h3>
                    <h4 className="font-semibold text-slate-900 mb-0.5 leading-snug">
                      {step.outcome}
                    </h4>
                    <p className="text-xs text-emerald-700 font-medium">
                      What you achieve
                    </p>
                  </div>
                </div>
              </div>

              <div className="rounded-lg border-2 border-red-300 bg-gradient-to-br from-red-50 to-white shadow px-4 pt-4 pb-3">
                <div className="flex items-start gap-2">
                  <div className="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 flex items-center justify-center mt-0.5">
                    <XCircle className="w-4 h-4 text-red-600" />
                  </div>
                  <div>
                    <h3 className="text-sm font-bold text-red-700 uppercase tracking-wide m-0 mb-2">
                      Magic Universal Wrapper Mistakes
                    </h3>
                    <h4 className="font-semibold text-slate-900 mb-0.5 leading-snug">
                      {step.antipattern}
                    </h4>
                    <p className="text-xs text-red-700 font-medium">
                      What to avoid
                    </p>
                  </div>
                </div>
              </div>
            </div>

            {/* Code Example Title and Navigation Row */}
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center gap-2">
                <FileCode className="w-4 h-4 text-slate-500" />
                <h3 className="text-xs font-bold text-slate-500 uppercase tracking-wider m-0">
                  Code Example
                </h3>
              </div>

              {/* Navigation Buttons */}
              <div className="flex items-center gap-3">
                {stepIndex > 0 && (
                  <button
                    onClick={() =>
                      setCurrentStep(workflowSteps[stepIndex - 1].id)
                    }
                    className="group px-6 py-2 rounded-lg font-semibold flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all duration-200"
                  >
                    <ArrowLeft className="w-4 h-4" />
                    Previous
                  </button>
                )}

                {stepIndex < workflowSteps.length - 1 ? (
                  <button
                    onClick={handleStepComplete}
                    className="group px-8 py-3 rounded-xl font-semibold flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white shadow-lg shadow-emerald-500/30 transition-all duration-200"
                  >
                    Next Step
                    <ArrowRight className="w-4 h-4" />
                  </button>
                ) : (
                  <button
                    onClick={handleStepComplete}
                    className="group px-8 py-3 rounded-xl font-semibold flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white shadow-lg shadow-emerald-500/30 transition-all duration-200"
                  >
                    Complete Workflow
                    <Sparkles className="w-4 h-4" />
                  </button>
                )}
              </div>
            </div>

            {/* Code Example */}
            <div className="bg-slate-900 rounded-2xl overflow-hidden shadow-2xl mb-8">
              <div className="flex items-center gap-2 px-5 py-3 bg-slate-800 border-b border-slate-700">
                <div className="flex gap-2">
                  <div className="w-3 h-3 rounded-full bg-red-500"></div>
                  <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                  <div className="w-3 h-3 rounded-full bg-green-500"></div>
                </div>
                <span className="text-xs text-slate-400 ml-3 font-mono">
                  CampaignCreate.php
                </span>
              </div>
              <pre className="p-6 overflow-x-auto text-sm leading-loose">
                <code className="text-slate-300 font-mono">{step.code}</code>
              </pre>
            </div>
          </div>
        </div>

        {/* Comparison Section */}
        {allStepsCompleted && (
          <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200 animate-fade-in">
            <div className="bg-gradient-to-r from-amber-400 via-amber-500 to-orange-500 p-4 text-center relative overflow-hidden">
              <div className="relative">
                <h2 className="text-xl font-bold text-slate-900 mb-1">
                  Two Approaches Compared
                </h2>
                <p className="text-sm text-slate-800">
                  Understanding the fundamental difference in philosophy
                </p>
              </div>
            </div>

            <div className="p-4">
              <div className="grid lg:grid-cols-2 gap-4">
                {/* Plenipotentiary Column */}
                <div className="p-8 rounded-2xl border-4 border-emerald-500 bg-gradient-to-br from-emerald-50 via-white to-emerald-50 shadow-xl hover:shadow-2xl transition-shadow duration-300">
                  <div className="mb-6 pb-6 border-b-2 border-emerald-200">
                    <div className="flex items-center gap-3 mb-3">
                      <CheckCircle className="w-8 h-8 text-emerald-600" />
                      <h3 className="text-2xl font-bold text-slate-900">
                        Plenipotentiary
                      </h3>
                    </div>
                    <p className="text-sm font-bold text-emerald-700 uppercase tracking-wide">
                      API-First Approach
                    </p>
                  </div>

                  <ol className="space-y-4 mb-8">
                    {[
                      {
                        text: "Copy real SDK example",
                        desc: "Start with provider documentation",
                      },
                      {
                        text: "Make real API call",
                        desc: "See actual responses",
                      },
                      {
                        text: "Define INPUT_SPEC from learning",
                        desc: "Codify what you discovered",
                      },
                      {
                        text: "Test until green",
                        desc: "Validate your understanding",
                      },
                      {
                        text: "Gateway shows required DTO",
                        desc: "Structure emerges from spec",
                      },
                      {
                        text: "Scaffold from YOUR spec",
                        desc: "Generate only what you need",
                      },
                      {
                        text: "Robustness layers on",
                        desc: "Add cross-cutting concerns last",
                      },
                    ].map((item, i) => (
                      <li key={i} className="flex gap-4">
                        <span className="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold shadow-md">
                          {i + 1}
                        </span>
                        <div className="pt-0.5">
                          <p className="text-slate-900 font-semibold leading-tight">
                            {item.text}
                          </p>
                          <p className="text-xs text-slate-600 mt-1">
                            {item.desc}
                          </p>
                        </div>
                      </li>
                    ))}
                  </ol>

                  <div className="p-5 bg-emerald-100 rounded-xl border-l-4 border-emerald-600 shadow-inner">
                    <p className="text-sm font-bold text-slate-900 mb-2">
                      Result: Understanding → Contract → Tooling
                    </p>
                    <p className="text-sm text-slate-700 italic leading-relaxed">
                      You know exactly what the API does and why your code
                      works.
                    </p>
                  </div>
                </div>

                {/* Historic Wrapper Column */}
                <div className="p-8 rounded-2xl border-4 border-red-500 bg-gradient-to-br from-red-50 via-white to-red-50 shadow-xl hover:shadow-2xl transition-shadow duration-300">
                  <div className="mb-6 pb-6 border-b-2 border-red-200">
                    <div className="flex items-center gap-3 mb-3">
                      <XCircle className="w-8 h-8 text-red-600" />
                      <h3 className="text-2xl font-bold text-slate-900">
                        Historic API Wrapper
                      </h3>
                    </div>
                    <p className="text-sm font-bold text-red-700 uppercase tracking-wide">
                      Try to Make Life "Easier" Approach
                    </p>
                  </div>

                  <ol className="space-y-4 mb-8">
                    {[
                      {
                        text: "Design perfect abstract DTOs",
                        desc: "Before knowing the API",
                      },
                      {
                        text: "Build universal mapper",
                        desc: "Trying to handle all cases",
                      },
                      {
                        text: "Add all possible fields",
                        desc: '"Just in case" mentality',
                      },
                      {
                        text: "Hide validation in framework",
                        desc: "Magic config files",
                      },
                      {
                        text: "Abstract before understanding",
                        desc: "Layers upon layers",
                      },
                      {
                        text: "Debug magic when it breaks",
                        desc: "Where is this error from?",
                      },
                      {
                        text: "Never really understand the API",
                        desc: "Perpetual confusion",
                      },
                    ].map((item, i) => (
                      <li key={i} className="flex gap-4">
                        <span className="flex-shrink-0 w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center text-sm font-bold shadow-md">
                          {i + 1}
                        </span>
                        <div className="pt-0.5">
                          <p className="text-slate-900 font-semibold leading-tight">
                            {item.text}
                          </p>
                          <p className="text-xs text-slate-600 mt-1">
                            {item.desc}
                          </p>
                        </div>
                      </li>
                    ))}
                  </ol>

                  <div className="p-5 bg-red-100 rounded-xl border-l-4 border-red-600 shadow-inner">
                    <p className="text-sm font-bold text-slate-900 mb-2">
                      Result: Abstraction → Confusion → Debugging
                    </p>
                    <p className="text-sm text-slate-700 italic leading-relaxed">
                      You're constantly fighting the framework and never sure
                      why things break.
                    </p>
                  </div>
                </div>
              </div>

              {/* Key Insight */}
              <div className="mt-10 p-6 bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-r-2xl shadow-md">
                <div className="flex gap-4">
                  <div className="text-3xl">🎯</div>
                  <div>
                    <h4 className="font-bold text-slate-900 mb-3 text-lg">
                      Key Insight
                    </h4>
                    <p className="text-slate-700 leading-relaxed">
                      Most frameworks hide the API behind abstractions,
                      promising to make integration "easier."
                      <strong className="text-slate-900">
                        {" "}
                        Plenipotentiary forces you to learn the API first
                      </strong>
                      , then provides structure for what you learned. The
                      result? You understand your integrations completely, and
                      when something breaks, you know exactly where to look.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      <style>{`
        @keyframes fade-in {
          from {
            opacity: 0;
            transform: translateY(20px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
        .animate-fade-in {
          animation: fade-in 0.6s ease-out;
        }
        @keyframes ping {
          75%, 100% {
            transform: scale(2);
            opacity: 0;
          }
        }
        .animate-ping {
          animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
      `}</style>
    </div>
  );
}
