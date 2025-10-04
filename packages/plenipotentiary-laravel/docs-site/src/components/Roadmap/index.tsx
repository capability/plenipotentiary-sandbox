import React, { useState } from "react";
import { MapPin, Target, Rocket, Layers, Sparkles, AlertTriangle, CheckCircle2, XCircle } from "lucide-react";

export default function Roadmap() {
  const [showCritique, setShowCritique] = useState(false);

  return (
    <div className="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-16 px-4">
      <div className="max-w-6xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center justify-center gap-3 mb-4">
            <MapPin className="w-10 h-10 text-emerald-600" />
            <h2 className="text-3xl font-bold text-slate-900 m-0">
              Why/Roadmap
            </h2>
          </div>
          <p className="text-lg text-slate-600 max-w-3xl mx-auto">
            The story behind Plenipotentiary and where it's heading
          </p>
        </div>

        {/* Honest Critique Section */}
        <div className="mb-8 bg-white rounded-2xl shadow-xl overflow-hidden border border-amber-400">
          <div className="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 px-6 py-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <AlertTriangle className="w-6 h-6 text-white" />
                <h2 className="text-xl font-bold text-white m-0">
                  The Elephant in the Room
                </h2>
              </div>
              <button
                onClick={() => setShowCritique(!showCritique)}
                className="px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-lg text-white font-semibold transition-all"
              >
                {showCritique ? "Hide" : "Show"} Honest Discussion
              </button>
            </div>
          </div>

          {showCritique && (
            <div className="p-6 space-y-6">
              <div className="prose max-w-none">
                <p className="text-base text-slate-700 leading-relaxed">
                  Let's be brutally honest about what people will think when they first see Plenipotentiary:
                </p>
              </div>

              {/* Perception vs Reality Grid */}
              <div className="grid md:grid-cols-2 gap-6">
                {/* Left Column - Common Objections */}
                <div className="space-y-4">
                  <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <XCircle className="w-5 h-5 text-red-600" />
                    Common First Reactions
                  </h3>

                  <div className="space-y-3">
                    <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        "This is just another API wrapper"
                      </p>
                      <p className="text-sm text-slate-700">
                        Like dozens that came before and failed. Remember the Guzzle service wrappers that got abandoned?
                      </p>
                    </div>

                    <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        "Why not just use Saloon directly?"
                      </p>
                      <p className="text-sm text-slate-700">
                        Saloon is proven, actively maintained, and simpler. Why add another layer?
                      </p>
                    </div>

                    <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        "Too much overhead for simple integrations"
                      </p>
                      <p className="text-sm text-slate-700">
                        Gateway, Adapter, DTO, Factory, INPUT_SPEC... that's a lot of files for one API call.
                      </p>
                    </div>

                    <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        "APIs don't actually change that often"
                      </p>
                      <p className="text-sm text-slate-700">
                        Stripe has rock-solid backwards compatibility. Google Ads supports versions for years. Is vendor churn really the problem?
                      </p>
                    </div>

                    <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        "This will be abandoned in 6 months"
                      </p>
                      <p className="text-sm text-slate-700">
                        One person maintaining scaffolding, patterns, docs, and examples for multiple providers? That's a lot to sustain.
                      </p>
                    </div>
                  </div>
                </div>

                {/* Right Column - Our Counter-Arguments */}
                <div className="space-y-4">
                  <h3 className="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <CheckCircle2 className="w-5 h-5 text-emerald-600" />
                    The Actual Reality
                  </h3>

                  <div className="space-y-3">
                    <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        Not a wrapper—it's architecture
                      </p>
                      <p className="text-sm text-slate-700">
                        You still implement the API integration. Plenipotentiary provides structure, not magic. The goal: make Google Ads (SDK) look like Mailchimp (REST) in YOUR system.
                      </p>
                    </div>

                    <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        Complements Saloon, doesn't replace it
                      </p>
                      <p className="text-sm text-slate-700">
                        Use Saloon for REST calls within your Adapter. Plenipotentiary wraps it in Gateway pattern for consistency across SDK, REST, SOAP, and future transport types.
                      </p>
                    </div>

                    <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        Overhead justified for multi-integration apps
                      </p>
                      <p className="text-sm text-slate-700">
                        1-2 integrations? Overkill. 5+ integrations mixing SDKs and REST? Plenipotentiary prevents chaos. It's about consistency at scale.
                      </p>
                    </div>

                    <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        It's not just about vendor churn
                      </p>
                      <p className="text-sm text-slate-700">
                        It's about isolation, testability, and uniform interfaces. When you have Google Ads SDK, Stripe SDK, Mailchimp REST, and legacy SOAP—Gateway pattern brings order.
                      </p>
                    </div>

                    <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                      <p className="font-semibold text-slate-900 mb-1">
                        Built from battle scars, not theory
                      </p>
                      <p className="text-sm text-slate-700">
                        This emerged from years of pain—especially Google's AdWords → Ads API migration. It's opinionated because it's lived experience, not academic exercise.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              {/* Who This Is For */}
              <div className="mt-8 p-6 bg-gradient-to-br from-blue-50 to-slate-50 border border-slate-300 rounded-xl">
                <h3 className="text-lg font-bold text-slate-900 mb-4">
                  Who Should Actually Use This?
                </h3>

                <div className="grid md:grid-cols-2 gap-6">
                  <div>
                    <p className="font-bold text-emerald-700 mb-3 flex items-center gap-2">
                      <CheckCircle2 className="w-4 h-4" />
                      Good Fit
                    </p>
                    <ul className="space-y-2 text-sm text-slate-700">
                      <li className="flex items-start gap-2">
                        <span className="text-emerald-600 font-bold">✓</span>
                        <span>Laravel apps with 5+ external API integrations</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <span className="text-emerald-600 font-bold">✓</span>
                        <span>Teams mixing SDKs (Google, Stripe) with REST APIs</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <span className="text-emerald-600 font-bold">✓</span>
                        <span>Projects expecting 3+ year lifespans</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <span className="text-emerald-600 font-bold">✓</span>
                        <span>Agencies building consistent integration patterns</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <span className="text-emerald-600 font-bold">✓</span>
                        <span>Developers who value explicit contracts over magic</span>
                      </li>
                    </ul>
                  </div>

                  <div>
                    <p className="font-bold text-red-700 mb-3 flex items-center gap-2">
                      <XCircle className="w-4 h-4" />
                      Probably Overkill
                    </p>
                    <ul className="space-y-2 text-sm text-slate-700">
                      <li className="flex items-start gap-2">
                        <span className="text-red-600 font-bold">✗</span>
                        <span>Single API integration (just use Saloon/SDK directly)</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <span className="text-red-600 font-bold">✗</span>
                        <span>MVPs and prototypes (premature architecture)</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <span className="text-red-600 font-bold">✗</span>
                        <span>Solo developers on small projects</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <span className="text-red-600 font-bold">✗</span>
                        <span>Teams allergic to structure and patterns</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <span className="text-red-600 font-bold">✗</span>
                        <span>Anyone looking for "quick and easy" magic solutions</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              {/* Final Honest Take */}
              <div className="mt-6 p-6 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl">
                <h3 className="text-lg font-bold text-slate-900 mb-3">
                  The Bottom Line
                </h3>
                <div className="space-y-3 text-slate-700 leading-relaxed">
                  <p>
                    <strong>Plenipotentiary is not for everyone.</strong> It's deliberately opinionated. It adds ceremony. It requires learning new patterns.
                  </p>
                  <p>
                    But if you've ever worked on a Laravel app with a dozen different API integrations—some SDKs, some REST, some SOAP nightmares—each implemented differently by different developers over the years, you know the chaos this prevents.
                  </p>
                  <p>
                    <strong>This isn't innovation.</strong> It's the Gateway pattern (from Domain-Driven Design) applied to Laravel API integrations. Nothing fancy. Just consistent structure for heterogeneous services.
                  </p>
                  <p>
                    <strong>Will it be actively maintained?</strong> That's the real question. Ambitious frameworks need sustained effort. This is one person's battle-tested approach, shared publicly. Use it, fork it, learn from it, or ignore it—but don't expect it to be the next Laravel Horizon.
                  </p>
                  <p className="font-semibold text-slate-900">
                    If you're drowning in integration chaos, Plenipotentiary offers a lifeboat. If you're swimming comfortably, you probably don't need it.
                  </p>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Why Plenipotentiary Exists */}
        <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200 mb-8">
          <div className="bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 px-6 py-4">
            <div className="flex items-center gap-3">
              <MapPin className="w-6 h-6 text-white" />
              <h2 className="text-xl font-bold text-white m-0">
                Why Plenipotentiary exists
              </h2>
            </div>
          </div>
          <div className="p-6">
            <div className="space-y-4 text-base text-slate-700 leading-relaxed">
              <p>
                I've spent my whole career making one system talk to another. Over
                those years PHP has improved, frameworks have matured, and our
                expectations of testing and code robustness have gone up. My
                earliest attempts, long before Laravel had a foothold, were
                brittle. I've thrown together integrations quickly; they worked,
                but they could have been better.
              </p>
              <p>
                I've also taken some hard knocks. When Google sunset the AdWords
                API on April 27, 2022 and moved to the Google Ads API, one of my
                deepest integrations, built 10 years earlier, effectively became a
                new project just to get back to where I was before. That
                experience reshaped how I build: better boundaries, cleaner
                contracts, and more attention to SDK churn.
              </p>
              <p>
                There are many ways to skin a cat, and I've tried most of them.
                What you see here is an opinionated way to keep your domain clean
                and testable while still relying on third-party code that will
                change. SDK churn is real. That quick script that gets you moving
                today can just as easily stall you in a few years' time.
              </p>
              <p>
                This is not the only way to build integrations. It's not even the
                best way. It's just my way. If you like it, great! If you keep
                building integrations a new way every time an API feels different,
                this opinionated structure will help. If you already have a strong
                approach, fantastic, share your experience and stick with it. And
                if you think it's a bad idea, fair enough.
              </p>
              <p>
                For me, I just wanted a tool that spins up a safe, predictable way
                to use a small slice of a big API or SDK without reinventing the
                guardrails every time. This is what I've come up with. And I
                thought I'd share it.
              </p>
              <p>
                It's one opinionated approach among many. Suggestions,
                considerations, critiques, and potential problems are welcome. PRs
                encouraged.
              </p>
            </div>
          </div>
        </div>

        {/* The Bigger Goal */}
        <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
          <div className="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 px-6 py-4">
            <div className="flex items-center gap-3">
              <Target className="w-6 h-6 text-white" />
              <h2 className="text-xl font-bold text-white m-0">
                Is there a bigger goal?
              </h2>
            </div>
          </div>
          <div className="p-6">
            <p className="text-base text-slate-700 mb-4 leading-relaxed">
              Yes: <strong>tiny</strong>, shareable adapters with explicit
              contracts. <strong>Ruthlessly small</strong>... only exposing the
              high-value slice of an API/SDK, not a mirror of the provider.
              Guzzle attempted community adapters and it proved to be a
              maintenance challenge. Perhaps it is a stretch too far. But teams
              working with the same provider API in different contexts could
              benefit from <strong>exposing DTO structure</strong> through
              INPUT_SPEC. Instantly see what data is required, what's validated,
              and how to construct payloads.
            </p>
            <div className="space-y-4">
              <div className="flex items-start gap-3">
                <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center flex-shrink-0 mt-1">
                  <Rocket className="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-slate-900 mb-1">
                    Zero integration, maximum tooling
                  </h3>
                  <p className="text-sm text-slate-700 leading-relaxed mb-0">
                    Install the adapter, run op through gateway, it fails with a
                    payload that shows the exact DTO (plus expected +
                    violations) and generates the Factory to match. You just
                    fill the values and go.
                  </p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center flex-shrink-0 mt-1">
                  <Layers className="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-slate-900 mb-1">
                    Not a community maintenance trap (abstraction-lite)
                  </h3>
                  <p className="text-sm text-slate-700 leading-relaxed mb-0">
                    Community adapters must keep the scope small and
                    task-focused. The goal is to cover the 20% of operations
                    that deliver 80% of value, not the entire API. If you need
                    more, copy the adapter code into your app and extend it. The
                    framework supports it... but that maintenance becomes yours.
                  </p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center flex-shrink-0 mt-1">
                  <Target className="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-slate-900 mb-1">
                    AI-Maintained Adapters?
                  </h3>
                  <p className="text-sm text-slate-700 leading-relaxed mb-3">
                    Would you trust an AI coding agent to maintain an entire API
                    integration for you? Probably not. Would you trust an AI
                    agent to maintain a single CRUD adapter for a Google Ads
                    Campaign resource where each verb has full test coverage and
                    success is deterministic? Perhaps.
                  </p>
                  <p className="text-sm text-slate-700 leading-relaxed mb-0">
                    Maybe this is the solution to the community maintenance
                    challenge. Small, focused, well-tested adapters that AI
                    agents can reliably maintain and update as provider APIs
                    evolve.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Future Roadmap */}
        <div className="mt-8 bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
          <div className="bg-gradient-to-r from-purple-500 via-purple-600 to-indigo-600 px-6 py-4">
            <div className="flex items-center gap-3">
              <Sparkles className="w-6 h-6 text-white" />
              <h2 className="text-xl font-bold text-white m-0">
                Roadmap: Future Possibilities
              </h2>
            </div>
          </div>
          <div className="p-6">
            <p className="text-base text-slate-700 mb-6 leading-relaxed">
              Features that aren't here yet but could expand Plenipotentiary's
              capabilities. These are possibilities, not promises.
            </p>
            <div className="grid md:grid-cols-2 gap-4">
              <div className="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h4 className="font-bold text-slate-900 mb-2">
                  OpenAPI to DTO Generator
                </h4>
                <p className="text-sm text-slate-700 mb-0">
                  Auto-generate CanonicalDTOs and INPUT_SPECs from OpenAPI
                  schemas. Point at a spec, get type-safe DTOs with validation
                  rules already mapped.
                </p>
              </div>
              <div className="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h4 className="font-bold text-slate-900 mb-2">
                  Workflow Integration
                </h4>
                <p className="text-sm text-slate-700 mb-0">
                  First-class support for{" "}
                  <a
                    href="https://github.com/laravel-workflow/laravel-workflow"
                    className="text-purple-600 hover:text-purple-700 underline"
                  >
                    Laravel Workflow
                  </a>
                  . Chain Gateway operations into durable workflows with
                  automatic retry and state management.
                </p>
              </div>
              <div className="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h4 className="font-bold text-slate-900 mb-2">
                  gRPC Transport Support
                </h4>
                <p className="text-sm text-slate-700 mb-0">
                  Extend beyond HTTP/REST. Add gRPC adapters with the same
                  Gateway pattern—stable contracts, swappable transport.
                </p>
              </div>
              <div className="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h4 className="font-bold text-slate-900 mb-2">
                  GraphQL Gateway Pattern
                </h4>
                <p className="text-sm text-slate-700 mb-0">
                  Apply Gateway architecture to GraphQL APIs. Type-safe queries,
                  fragment reuse, and the same anti-corruption layer benefits.
                </p>
              </div>
              <div className="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h4 className="font-bold text-slate-900 mb-2">
                  Event-Driven Adapters
                </h4>
                <p className="text-sm text-slate-700 mb-0">
                  Webhooks and streaming APIs as first-class patterns. Handle
                  inbound events with the same stability guarantees as outbound
                  operations.
                </p>
              </div>
              <div className="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h4 className="font-bold text-slate-900 mb-2">
                  AI-Powered Integration Builder
                </h4>
                <p className="text-sm text-slate-700 mb-0">
                  Describe your integration in natural language, let AI analyze
                  provider docs, generate DTOs, scaffold adapters, write tests,
                  and produce integration logic. MCP Tools + Gateway contracts =
                  AI that understands your entire integration surface.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
