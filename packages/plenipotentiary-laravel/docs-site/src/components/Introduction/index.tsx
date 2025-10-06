import React, { useState, useEffect } from "react";
import useBaseUrl from "@docusaurus/useBaseUrl";
import {
  BookOpen,
  CheckCircle,
  Layers,
  Shield,
  AlertCircle,
  Network,
  Target,
  Terminal,
} from "lucide-react";

interface Example {
  title: string;
  command: string;
}

const examples: Example[] = [
  {
    title: "Stripe Payment Gateway",
    command:
      "php artisan pleni:make:crud \\\n  --provider=Stripe \\\n  --domain=Billing \\\n  --resource=Customer \\\n  --with-actions \\\n  --with-tests",
  },
  {
    title: "eBay Product Search",
    command:
      "php artisan pleni:make:operation \\\n  --provider=eBay \\\n  --domain=Browse \\\n  --resource=SearchItems \\\n  --with-controller \\\n  --with-tests",
  },
  {
    title: "GitHub API Integration",
    command:
      "php artisan pleni:make:rest \\\n  --provider=GitHub \\\n  --domain=API \\\n  --resource=Repositories \\\n  --with-requests \\\n  --with-tests",
  },
  {
    title: "Internal Admin Tool",
    command:
      "php artisan pleni:make:procedure \\\n  --provider=InternalAPI \\\n  --domain=Admin \\\n  --resource=SendAlert \\\n  --with-commands",
  },
  {
    title: "MCP Tool for AI Agents",
    command:
      "php artisan pleni:make:mcp-tool \\\n  --server=customer-database \\\n  --tool=get_customer_orders \\\n  --with-policies \\\n  --with-tests",
  },
];

export default function Introduction() {
  const logoUrl = useBaseUrl("/img/pleni_logo.svg");
  const [currentExampleIndex, setCurrentExampleIndex] = useState(0);
  const [displayedText, setDisplayedText] = useState("");
  const [isTyping, setIsTyping] = useState(true);

  useEffect(() => {
    const currentExample = examples[currentExampleIndex];
    const fullCommand = currentExample.command;

    if (isTyping) {
      if (displayedText.length < fullCommand.length) {
        const timeout = setTimeout(() => {
          setDisplayedText(fullCommand.slice(0, displayedText.length + 1));
        }, 30); // Typing speed
        return () => clearTimeout(timeout);
      } else {
        // Finished typing, wait before clearing
        const timeout = setTimeout(() => {
          setIsTyping(false);
        }, 2000);
        return () => clearTimeout(timeout);
      }
    } else {
      // Clear and move to next example
      const timeout = setTimeout(() => {
        setDisplayedText("");
        setCurrentExampleIndex((prev) => (prev + 1) % examples.length);
        setIsTyping(true);
      }, 500);
      return () => clearTimeout(timeout);
    }
  }, [displayedText, currentExampleIndex, isTyping]);

  return (
    <div className="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-12 px-4">
      <div className="max-w-6xl mx-auto">
        {/* Logo & Problem Section */}
        <div className="bg-white rounded-2xl shadow-xl p-8 mb-8 border-2 border-slate-300">
          <div className="grid md:grid-cols-2 gap-8 items-center">
            {/* Left: Logo and Tagline */}
            <div className="flex flex-col justify-center items-center">
              <img
                src={logoUrl}
                alt="Plenipotentiary"
                className="mb-2"
                style={{ maxWidth: "280px", height: "auto" }}
              />
              <h1 className="text-4xl font-bold text-slate-900 mb-4 tracking-tight">
                Plenipotentiary
              </h1>
              <p className="text-xl text-slate-600 text-center font-medium">
                Scaffolding and patterns for Laravel API/SDK integrations
              </p>
            </div>

            {/* Right: The Challenge */}
            <div>
              <h3 className="text-2xl font-bold text-slate-900 mb-4">
                The Integration Challenge
              </h3>
              <p className="text-base text-slate-700 leading-relaxed mb-6">
                Your Laravel app needs to integrate with{" "}
                <strong className="text-emerald-700">
                  5-15 external services
                </strong>
                : payment gateways, CRMs, advertising platforms, and more. Each
                one uses a different approach: official SDKs, REST APIs, SOAP,
                or emerging protocols like MCP.
              </p>
              <p className="text-base text-slate-700 leading-relaxed mb-6">
                Over <strong className="text-emerald-700">3-5 years</strong> and{" "}
                <strong className="text-emerald-700">3-10 developers</strong>,
                these integrations become a{" "}
                <strong>maintenance nightmare</strong>. Scattered patterns,
                inconsistent error handling, and business logic tightly coupled
                to vendor implementations.
              </p>
              <div className="bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-r-lg p-4">
                <p className="text-base font-semibold text-slate-900 mb-2">
                  The Core Problem:
                </p>
                <p className="text-base text-slate-700">
                  How do you{" "}
                  <strong>
                    maintain consistency across heterogeneous integrations
                  </strong>{" "}
                  without losing access to provider-specific features or
                  building leaky abstractions?
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Goal & What This Is NOT */}
        <div className="grid md:grid-cols-2 gap-8 mb-8">
          {/* Left: Goal */}
          <div className="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl shadow-lg p-6 border-l-4 border-emerald-500">
            <h3 className="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
              <Target className="w-6 h-6 text-emerald-600" />
              Goal
            </h3>
            <p className="text-base text-slate-700 leading-relaxed mb-4">
              <strong className="text-emerald-700">
                Uniform interface for heterogeneous integrations.
              </strong>
            </p>
            <p className="text-base text-slate-700 leading-relaxed mb-4">
              Provide <strong>architectural patterns and tooling</strong> so
              that all integrations look consistent from the app's perspective,
              while allowing full access to underlying APIs.
            </p>
            <p className="text-base font-semibold text-slate-900 mb-3">
              This means:
            </p>
            <ul className="text-base text-slate-700 space-y-2 pl-4">
              <li className="list-disc">
                <strong>Consistency</strong> across SDK, REST, SOAP, MCP
                integrations
              </li>
              <li className="list-disc">
                <strong>Predictability</strong> in how your app interacts with
                ANY external service
              </li>
              <li className="list-disc">
                <strong>Testability</strong> - same mocking strategy for all
                integrations
              </li>
              <li className="list-disc">
                <strong>Discoverability</strong> - new dev sees Gateway pattern
                everywhere
              </li>
              <li className="list-disc">
                <strong>Swappability</strong> - change providers without
                touching business logic
              </li>
            </ul>
          </div>

          {/* Right: What This Is NOT */}
          <div className="bg-gradient-to-r from-red-50 to-orange-50 rounded-2xl shadow-lg p-6 border-l-4 border-red-500">
            <h3 className="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
              <AlertCircle className="w-6 h-6 text-red-600" />
              What This Is NOT
            </h3>
            <ul className="text-base text-slate-700 space-y-2 pl-4">
              <li className="list-disc">
                <strong>Not an "API wrapper for X"</strong> - Wrappers promise
                complete coverage but deliver 20-40%. They can't keep up with
                API evolution.
              </li>
              <li className="list-disc">
                <strong>Not a "unified API client"</strong> - REST is a
                philosophy, not a protocol. Universal abstraction is impossible
                without losing unique provider value.
              </li>
              <li className="list-disc">
                <strong>Not complete endpoint coverage</strong> - It provides
                architecture and patterns. You implement the adapters you need.
              </li>
              <li className="list-disc">
                <strong>Not hiding complexity</strong> - Leaky abstractions
                break under real use. We provide structure, not magic.
              </li>
              <li className="list-disc">
                <strong>Not code generation for its own sake</strong> -
                Generated code is only valuable if it's maintainable and fits
                your architecture.
              </li>
            </ul>
            <p className="text-sm text-slate-600 mt-4 italic">
              Even API creators struggle to maintain good SDKs. Third-party
              wrappers are doomed from the start. Plenipotentiary gives you
              patterns, not promises.
            </p>
          </div>
        </div>

        {/* Gateway Pattern Benefits */}
        <div className="bg-white rounded-2xl shadow-lg p-8 mb-8 border border-slate-200">
          <h3 className="text-2xl font-bold text-slate-900 mb-6 text-center flex items-center justify-center gap-2 flex-wrap">
            <Shield className="w-6 h-6 md:w-7 md:h-7 text-emerald-600 flex-shrink-0" />
            <span>The Gateway Pattern: Your Architectural Anchor</span>
          </h3>

          <div className="grid md:grid-cols-2 gap-8">
            {/* Left: Why Gateway Pattern */}
            <div className="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-6 border-l-4 border-emerald-500">
              <h4 className="text-xl font-bold text-slate-900 mb-4">
                Why This Pattern?
              </h4>
              <p className="text-base text-slate-700 leading-relaxed mb-4">
                The <strong>Gateway/Adapter pattern</strong> provides a stable
                interception point between your application and external
                services. Once this structure is in place, you gain a{" "}
                <strong>single, consistent location</strong> to layer in
                production-grade features.
              </p>
              <p className="text-base text-slate-700 leading-relaxed mb-4">
                Without it, these concerns scatter across controllers, jobs, and
                service classes. Impossible to maintain consistently across 5-15
                different integrations.
              </p>
              <p className="text-base text-emerald-800 font-semibold italic">
                The pattern isn't magic; it's discipline. It gives you one place
                to solve each problem, once, for all integrations.
              </p>
            </div>

            {/* Right: What You Get */}
            <div>
              <h4 className="text-xl font-bold text-slate-900 mb-4">
                What You Can Layer In
              </h4>
              <p className="text-base text-slate-600 mb-4">
                Not every integration needs all of these. A simple lookup needs
                none. A financial integration needs them all.
              </p>
              <div className="space-y-3">
                <div className="flex items-start gap-3">
                  <span className="text-emerald-600 font-bold text-base min-w-[24px]">
                    1.
                  </span>
                  <div className="text-base text-slate-700">
                    <strong>Validation</strong> - Your app's requirements
                    enforced before the call
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <span className="text-emerald-600 font-bold text-base min-w-[24px]">
                    2.
                  </span>
                  <div className="text-base text-slate-700">
                    <strong>Idempotency</strong> - Safe retries, no duplicate
                    charges
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <span className="text-emerald-600 font-bold text-base min-w-[24px]">
                    3.
                  </span>
                  <div className="text-base text-slate-700">
                    <strong>Test coverage</strong> - High confidence in
                    production behavior
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <span className="text-emerald-600 font-bold text-base min-w-[24px]">
                    4.
                  </span>
                  <div className="text-base text-slate-700">
                    <strong>Persistence</strong> - Audit trail for debugging and
                    compliance
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <span className="text-emerald-600 font-bold text-base min-w-[24px]">
                    5.
                  </span>
                  <div className="text-base text-slate-700">
                    <strong>Policy enforcement</strong> - Rate limits, budgets,
                    approval workflows
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <span className="text-emerald-600 font-bold text-base min-w-[24px]">
                    6.
                  </span>
                  <div className="text-base text-slate-700">
                    <strong>Error handling</strong> - Retries, circuit breakers,
                    graceful degradation
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <span className="text-emerald-600 font-bold text-base min-w-[24px]">
                    7.
                  </span>
                  <div className="text-base text-slate-700">
                    <strong>Observability</strong> - Logging, metrics,
                    distributed tracing
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="grid md:grid-cols-3 gap-6 mb-6">
          {/* TL;DR */}
          <div className="md:col-span-2">
            <div className="bg-white rounded-2xl shadow-lg p-6 border border-slate-200 h-full">
              <div className="flex items-center gap-2 mb-4">
                <BookOpen className="w-6 h-6 text-emerald-600" />
                <h2 className="text-2xl font-bold text-slate-900 m-0">TL;DR</h2>
              </div>
              <p className="text-base text-slate-700 mb-4 leading-relaxed">
                Think of Pleni like{" "}
                <code className="bg-slate-100 px-2 py-0.5 rounded text-base text-slate-800">
                  artisan:make
                </code>{" "}
                for third-party APIs: declare the provider, domain, context, and
                resource and instantly scaffold the DTOs, gateways, adapters and
                test harness you need. Plenipotentiary provides the contracts.
                You still implement the adapter logic (it's not magic), but the
                code now sits in a consistent, testable, tool-friendly
                structure.
              </p>
              <p className="text-base text-slate-700 leading-relaxed mb-0">
                Flysystem-style consistency for APIs, while recognizing not
                everything should be abstracted. Flysystem works because
                filesystems share common verbs. APIs don't: REST is philosophy,
                SDKs are vendor-specific, SOAP is legacy. Plenipotentiary gives
                them all the same architectural pattern, so your app doesn't
                care what's underneath.
              </p>
            </div>
          </div>

          {/* Dictionary Definition */}
          <div className="md:col-span-1">
            <div className="bg-white rounded-2xl shadow-lg p-6 border border-slate-200 h-full">
              <h3 className="text-xl font-bold text-slate-900 mb-3">
                plenipotentiary
              </h3>
              <div className="text-base text-slate-500 mb-4">
                /ˌplɛnɪpəˈtɛn(t)ʃ(ə)ri/
              </div>
              <p className="text-base text-slate-700 leading-relaxed mb-0">
                a person{" "}
                <code className="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded text-base">
                  GATEWAY/ADAPTER
                </code>
                , invested with the full power of independent action on behalf
                of their government{" "}
                <code className="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-base">
                  DOMAIN
                </code>
                , typically in a foreign country{" "}
                <code className="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-base">
                  API_PROVIDER
                </code>
                .
              </p>
            </div>
          </div>
        </div>

        {/* Typing Command Example */}
        <div className="mb-6">
          <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
            <div className="grid md:grid-cols-2">
              {/* Left: Example List */}
              <div className="bg-gradient-to-br from-slate-50 to-slate-100 p-6 border-r border-slate-200">
                <div className="flex items-center gap-2 mb-4">
                  <Layers className="w-5 h-5 text-emerald-600" />
                  <h3 className="text-lg font-bold text-slate-900 m-0">
                    Quick Start Examples
                  </h3>
                </div>
                <div className="space-y-3">
                  {examples.map((example, index) => (
                    <div
                      key={index}
                      className={`p-3 rounded-lg transition-all duration-300 ${
                        index === currentExampleIndex
                          ? "bg-emerald-500 text-white shadow-md"
                          : "bg-white text-slate-700"
                      }`}
                    >
                      <div className="font-semibold text-sm">
                        {example.title}
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Right: Terminal */}
              <div className="bg-slate-900">
                <div className="flex items-center gap-2 px-5 py-3 bg-slate-800 border-b border-slate-700">
                  <div className="flex gap-2">
                    <div className="w-3 h-3 rounded-full bg-red-500"></div>
                    <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div className="w-3 h-3 rounded-full bg-green-500"></div>
                  </div>
                  <Terminal className="w-4 h-4 text-slate-400 ml-2" />
                  <span className="text-xs text-slate-400 font-mono">
                    terminal
                  </span>
                </div>
                <div className="p-6 h-[240px] flex items-center">
                  <pre className="text-sm leading-loose m-0 w-full">
                    <code className="text-emerald-400 font-mono whitespace-pre-wrap">
                      {displayedText}
                      <span className="inline-block w-2 h-5 bg-emerald-400 ml-1 animate-pulse"></span>
                    </code>
                  </pre>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
