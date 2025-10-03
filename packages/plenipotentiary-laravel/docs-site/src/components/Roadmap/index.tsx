import React from "react";
import { MapPin, Target, Rocket, Layers, Sparkles } from "lucide-react";

export default function Roadmap() {
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

        {/* Why Plenipotentiary Exists */}
        <div className="bg-white rounded-2xl shadow-xl p-8 border border-slate-200 mb-8">
          <h3 className="text-2xl font-bold text-slate-900 mb-6">
            Why Plenipotentiary exists
          </h3>

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
                    AI coding agents love constraints
                  </h3>
                  <p className="text-sm text-slate-700 leading-relaxed mb-0">
                    AI agents excel with repeatable patterns but can run wild
                    with too much freedom. By narrowing API/SDK interaction
                    through stable Gateway contracts and explicit DTOs, the
                    integration logic practically writes itself—complete with
                    test coverage. Predictable structure means predictable code
                    generation. Give an agent boundaries, and watch it deliver
                    production-ready integrations.
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
