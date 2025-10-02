import React, { useState } from "react";
import  CheckCirclefrom 'lucide-react';

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
  subtitle: string;
  principle: string;
  description: React.ReactNode;
  code: string;
  outcome: string;
  antipattern: string;
  icon: string;
}
import useBaseUrl from "@docusaurus/useBaseUrl";
const workflowSteps: Step[] = [
  {
    id: "understand",
    number: 1,
    title: "Start with the Real API",
    subtitle: "Understanding Over Abstraction",
    principle: "Learn the provider SDK/API first. No premature abstraction.",
    description: (
      <>
        Open <strong>one file, one operation</strong>. Copy the provider's SDK
        example almost verbatim. Make a real API call. See what comes back.{" "}
        <em>Understand</em> before you abstract.
      </>
    ),
    code: `// CampaignCreate.php - Start here, all in one place
public function perform(array $input): Result
{
    // Paste Google Ads SDK example, make it work
    $campaign = new Campaign([
        'name' => $input['name'],
        'budget' => $input['budget'],
        'status' => CampaignStatus::PAUSED,
    ]);
    
    $operation = new CampaignOperation();
    $operation->setCreate($campaign);
    
    // Real API call - see what happens!
    $response = $this->client
        ->getCampaignServiceClient()
        ->mutateCampaigns($customerId, [$operation]);
    
    // Mock response for now
    return Result::ok(['id' => '12345']);
}`,
    outcome: "✅ You understand the API call flow",
    antipattern: "❌ Starting with abstractions before understanding the API",
    icon: "🔍",
  },
  {
    id: "codify",
    number: 2,
    title: "Define Your INPUT_SPEC",
    subtitle: "Explicit Contract",
    principle: "Codify the minimum data you need. This is YOUR contract.",
    description: (
      <>
        Identify what fields you <strong>actually need</strong> from your
        business use case. Not everything the API supports - just what{" "}
        <em>you</em> need. Write it as <code>INPUT_SPEC</code>.
      </>
    ),
    code: `// CampaignCreate.php
public const INPUT_SPEC = [
    'name' => [
        'rules' => ['required', 'string', 'max:255'],
    ],
    'budget' => [
        'rules' => ['required', 'integer', 'min:1000'],
    ],
    'status' => [
        'rules' => ['in:ENABLED,PAUSED'],
        'default' => 'PAUSED',
    ],
    // Only what YOU need for YOUR use case
];

public static function inputSpec(): array
{
    return self::INPUT_SPEC;
}`,
    outcome: "✅ Explicit, auditable contract visible in code",
    antipattern:
      '❌ Hidden validation, magic field mappings, "the framework handles it"',
    icon: "📋",
  },
  {
    id: "test",
    number: 3,
    title: "Test Until Green",
    subtitle: "Stay in One Place",
    principle: "Write requestMapper() and responseMapper() until tests pass.",
    description: (
      <>
        Keep everything in the operation file. Build the request from validated
        input. Map the response back to your domain. Test{" "}
        <strong>success, validation, and errors</strong>. Don't move on until
        it's green.
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
    outcome: "✅ Operation tested, API understood, confidence gained",
    antipattern: "❌ Moving to gateway before understanding the operation",
    icon: "✅",
  },
  {
    id: "gateway",
    number: 4,
    title: "Call Through Gateway",
    subtitle: "Stable Boundary Emerges",
    principle: "Gateway reveals the DTO contract based on YOUR INPUT_SPEC.",
    description: (
      <>
        Run through the gateway for the first time. It will{" "}
        <strong>fail intentionally</strong> and show you what DTO and Factory to
        create - derived from <em>your</em> INPUT_SPEC, not magic.
      </>
    ),
    code: `// From your Action/Controller/Job
$result = $gateway->create([
    'name' => 'Summer Sale',
    'budget' => 50000,
]);

// Gateway error response shows you:
{
  "error": "CanonicalDTO not found",
  "required_dto": "CampaignCanonicalDTO",
  "required_factory": "CampaignCanonicalFactory",
  "spec": {
    "name": "required|string|max:255",
    "budget": "required|integer|min:1000",
    "status": "in:ENABLED,PAUSED (default: PAUSED)"
  }
}

// Gateway normalizes everything to Result
interface Result {
    isOk(): bool;
    isErr(): bool;
    value(): mixed;  // Your domain data
    error(): array;  // Normalized errors
}`,
    outcome: "✅ Gateway boundary defined, contract visible",
    antipattern: "❌ Premature DTO design before knowing what the API needs",
    icon: "🚪",
  },
  {
    id: "scaffold",
    number: 5,
    title: "Scaffold to Your Spec",
    subtitle: "Tooling Follows Understanding",
    principle: "Generate DTO/Factory from the INPUT_SPEC you wrote.",
    description: (
      <>
        Copy/generate the DTO and Factory shown in the gateway error. They're
        not guesses - they're <strong>derived from your INPUT_SPEC</strong>. The
        spec you wrote while working with the real API.
      </>
    ),
    code: `// CampaignCanonicalDTO.php - Generated from YOUR spec
final class CampaignCanonicalDTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $budget,
        public readonly string $status = 'PAUSED',
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            budget: $data['budget'],
            status: $data['status'] ?? 'PAUSED',
        );
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
    outcome: "✅ Type-safe contracts scaffolded from your understanding",
    antipattern: "❌ Auto-generated DTOs that include fields you don't need",
    icon: "🗂️",
  },
  {
    id: "robust",
    number: 6,
    title: "Robustness Comes Online",
    subtitle: "Progressive Enhancement",
    principle: "Cross-cutting concerns layer on top automatically.",
    description: (
      <>
        With the Gateway boundary in place, you <strong>automatically</strong>{" "}
        get robustness features. They weren't there when you were learning the
        API. They appear when you need them.
      </>
    ),
    code: `// Idempotency (automatic)
$result = $gateway->create($dto, 
    idempotencyKey: 'campaign-' . $dto->name
);

// Validation (from INPUT_SPEC)
$result = $gateway->create($dto);
if ($result->isInvalid()) {
    return $result->errors(); // ['budget' => 'must be >= 1000']
}

// Error Mapping (provider → domain)
if ($result->isErr()) {
    // GoogleAdsError → DomainError
    return $result->error(); // Normalized structure
}

// Queueing (Laravel integration)
dispatch(new CreateCampaignJob($dto));

// Observability (automatic)
// All calls logged, metrics tracked, errors mapped`,
    outcome:
      "✅ Production-ready with idempotency, validation, queueing, logging",
    antipattern:
      "❌ Adding cross-cutting concerns while still learning the API",
    icon: "🛡️",
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

  const isStepAccessible = (stepId: WorkflowPhase, index: number): boolean => {
    if (index === 0) return true;
    const prevStep = workflowSteps[index - 1];
    return completedSteps.has(prevStep.id);
  };

  const allStepsCompleted = completedSteps.size === workflowSteps.length;

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-12 px-4">
      <div className="max-w-5xl mx-auto">
        {/* Header */}
        <div className="text-center mb-10">
          <h1 className="text-4xl font-bold text-slate-900 mb-3">
            API-First Workflow
          </h1>
          <p className="text-lg text-slate-600 max-w-2xl mx-auto mb-6">
            Learn the real API first. Codify your understanding. Let tooling
            follow your spec.
          </p>
          <div className="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 text-white rounded-full font-semibold shadow-lg shadow-emerald-500/30 text-sm">
            <div className="w-2 h-2 bg-white rounded-full animate-pulse"></div>
            Core Principle: Understanding Over Abstraction
          </div>
        </div>

        {/* Progress Steps */}
        <div className="mb-12">
          <div className="flex items-center justify-center gap-2">
            {workflowSteps.map((s, index) => {
              const isActive = s.id === currentStep;
              const isCompleted = completedSteps.has(s.id);
              const isAccessible = isStepAccessible(s.id, index);

              return (
                <React.Fragment key={s.id}>
                  <button
                    onClick={() => isAccessible && handleStepSelect(s.id)}
                    disabled={!isAccessible}
                    className={`
                      w-14 h-14 rounded-full flex items-center justify-center text-2xl
                      transition-all duration-300 relative
                      ${
                        isActive
                          ? "bg-emerald-500 shadow-lg shadow-emerald-500/50 scale-110"
                          : ""
                      }
                      ${isCompleted && !isActive ? "bg-emerald-500" : ""}
                      ${!isActive && !isCompleted ? "bg-slate-200" : ""}
                      ${
                        !isAccessible
                          ? "opacity-40 cursor-not-allowed"
                          : "cursor-pointer hover:scale-105"
                      }
                    `}
                    title={s.title}
                  >
                    <span>{s.icon}</span>
                  </button>
                  {index < workflowSteps.length - 1 && (
                    <div
                      className={`w-12 h-1 rounded-full transition-colors duration-300 ${
                        isCompleted ? "bg-emerald-500" : "bg-slate-300"
                      }`}
                    ></div>
                  )}
                </React.Fragment>
              );
            })}
          </div>
        </div>

        {/* Main Content Card */}
        <div className="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
          {/* Step Header */}
          <div className="bg-gradient-to-r from-emerald-500 to-emerald-600 p-6 text-white">
            <div className="flex items-center gap-3 mb-2">
              <span className="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold uppercase tracking-wide">
                Step {step.number} of 6
              </span>
            </div>
            <h2 className="text-2xl font-bold mb-1">{step.title}</h2>
            <p className="text-emerald-100">{step.subtitle}</p>
          </div>

          {/* Content */}
          <div className="p-8">
            {/* Principle Box */}
            <div className="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg mb-6">
              <div className="flex gap-3">
                <div className="text-blue-500 text-xl">💡</div>
                <div>
                  <p className="text-blue-900 font-semibold text-sm mb-1">
                    Principle:
                  </p>
                  <p className="text-blue-800 text-sm">{step.principle}</p>
                </div>
              </div>
            </div>

            {/* Instructions */}
            <div className="mb-6 text-slate-700 leading-relaxed">
              {step.description}
            </div>

            {/* Code Example */}
            <div className="mb-6">
              <h3 className="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
                Code Example
              </h3>
              <div className="bg-slate-900 rounded-xl overflow-hidden shadow-lg">
                <div className="flex items-center gap-2 px-4 py-2.5 bg-slate-800 border-b border-slate-700">
                  <div className="flex gap-1.5">
                    <div className="w-3 h-3 rounded-full bg-red-500"></div>
                    <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div className="w-3 h-3 rounded-full bg-green-500"></div>
                  </div>
                  <span className="text-xs text-slate-400 ml-2">PHP</span>
                </div>
                <pre className="p-5 overflow-x-auto text-sm leading-relaxed">
                  <code className="text-slate-300">{step.code}</code>
                </pre>
              </div>
            </div>

            {/* Selection Options */}
            <div className="grid md:grid-cols-2 gap-4 mb-6">
              <div className="p-5 rounded-xl border-2 border-emerald-200 bg-emerald-50">
                <div className="flex items-start gap-3">
                  <CheckCircle className="w-5 h-5 text-emerald-500 mt-0.5 flex-shrink-0" />
                  <div>
                    <h4 className="font-semibold text-slate-900 mb-1 text-sm">
                      {step.outcome}
                    </h4>
                  </div>
                </div>
              </div>

              <div className="p-5 rounded-xl border-2 border-red-200 bg-red-50">
                <div className="flex items-start gap-3">
                  <XCircle className="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" />
                  <div>
                    <h4 className="font-semibold text-slate-900 mb-1 text-sm">
                      {step.antipattern}
                    </h4>
                  </div>
                </div>
              </div>
            </div>

            {/* Navigation Buttons */}
            <div className="flex justify-between items-center">
              {stepIndex > 0 ? (
                <button
                  onClick={() =>
                    setCurrentStep(workflowSteps[stepIndex - 1].id)
                  }
                  className="px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all duration-200"
                >
                  <ArrowLeft className="w-4 h-4" />
                  Previous
                </button>
              ) : (
                <div></div>
              )}

              {stepIndex < workflowSteps.length - 1 ? (
                <button
                  onClick={handleStepComplete}
                  className="px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/30 hover:shadow-xl transition-all duration-200"
                >
                  {completedSteps.has(currentStep)
                    ? "Next Step"
                    : "Mark Understood & Continue"}
                  <ArrowRight className="w-4 h-4" />
                </button>
              ) : (
                <button
                  onClick={handleStepComplete}
                  className="px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/30 hover:shadow-xl transition-all duration-200"
                >
                  Complete Workflow ✨
                </button>
              )}
            </div>
          </div>
        </div>

        {/* Comparison - Shows when all steps completed */}
        {allStepsCompleted && (
          <div className="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">
            <div className="bg-gradient-to-r from-amber-400 to-amber-500 p-6 text-center">
              <h2 className="text-2xl font-bold text-slate-900 mb-2">
                Two Approaches Compared
              </h2>
              <p className="text-slate-700">
                Understanding the fundamental difference in philosophy
              </p>
            </div>
            <div className="p-8">
              <div className="grid md:grid-cols-2 gap-6">
                {/* Plenipotentiary Column */}
                <div className="p-6 rounded-xl border-3 border-emerald-500 bg-gradient-to-br from-emerald-50 to-white">
                  <div className="mb-4 pb-4 border-b-2 border-emerald-200">
                    <h3 className="text-xl font-bold text-slate-900 mb-1">
                      ✅ Plenipotentiary
                    </h3>
                    <p className="text-sm font-semibold text-emerald-700">
                      API-First Approach
                    </p>
                  </div>
                  <ol className="space-y-3 mb-6">
                    {[
                      "Copy real SDK example",
                      "Make real API call",
                      "Define INPUT_SPEC from learning",
                      "Test until green",
                      "Gateway shows required DTO",
                      "Scaffold from YOUR spec",
                      "Robustness layers on",
                    ].map((item, i) => (
                      <li key={i} className="flex gap-3 text-sm">
                        <span className="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">
                          {i + 1}
                        </span>
                        <span className="text-slate-700 pt-0.5">{item}</span>
                      </li>
                    ))}
                  </ol>
                  <div className="p-4 bg-emerald-100/50 rounded-lg border-l-4 border-emerald-500">
                    <p className="text-sm font-semibold text-slate-900 mb-2">
                      Result: Understanding → Contract → Tooling
                    </p>
                    <p className="text-xs text-slate-600 italic">
                      You know exactly what the API does and why your code
                      works.
                    </p>
                  </div>
                </div>

                {/* Historic Wrapper Column */}
                <div className="p-6 rounded-xl border-3 border-red-500 bg-gradient-to-br from-red-50 to-white">
                  <div className="mb-4 pb-4 border-b-2 border-red-200">
                    <h3 className="text-xl font-bold text-slate-900 mb-1">
                      ❌ Historic API Wrapper
                    </h3>
                    <p className="text-sm font-semibold text-red-700">
                      Try to Make Life "Easier" Approach
                    </p>
                  </div>
                  <ol className="space-y-3 mb-6">
                    {[
                      "Design perfect abstract DTOs",
                      "Build universal mapper",
                      "Add all possible fields",
                      "Hide validation in framework",
                      "Abstract before understanding",
                      "Debug magic when it breaks",
                      "Never really understand the API",
                    ].map((item, i) => (
                      <li key={i} className="flex gap-3 text-sm">
                        <span className="flex-shrink-0 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center text-xs font-bold">
                          {i + 1}
                        </span>
                        <span className="text-slate-700 pt-0.5">{item}</span>
                      </li>
                    ))}
                  </ol>
                  <div className="p-4 bg-red-100/50 rounded-lg border-l-4 border-red-500">
                    <p className="text-sm font-semibold text-slate-900 mb-2">
                      Result: Abstraction → Confusion → Debugging
                    </p>
                    <p className="text-xs text-slate-600 italic">
                      You're constantly fighting the framework and never sure
                      why things break.
                    </p>
                  </div>
                </div>
              </div>

              <div className="mt-8 p-5 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl">
                <h4 className="font-bold text-slate-900 mb-2">
                  🎯 Key Insight
                </h4>
                <p className="text-sm text-slate-700 leading-relaxed">
                  Most frameworks hide the API behind abstractions, promising to
                  make integration "easier."
                  <strong>
                    {" "}
                    Plenipotentiary forces you to learn the API first
                  </strong>
                  , then provides structure for what you learned. The result?
                  You understand your integrations completely, and when
                  something breaks, you know exactly where to look.
                </p>
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
      `}</style>
    </div>
  );
}
