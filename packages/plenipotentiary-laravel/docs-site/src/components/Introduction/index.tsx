import React, { useState, useEffect } from "react";
import useBaseUrl from "@docusaurus/useBaseUrl";
import {
  BookOpen,
  CheckCircle,
  Layers,
  Shield,
  AlertCircle,
  Network,
  Sparkles,
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
  const logoUrl = useBaseUrl("/img/logo-words-1024.png");
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
        {/* Logo */}
        <div className="text-center mb-8">
          <img
            src={logoUrl}
            alt="Plenipotentiary"
            className="mx-auto mb-4"
            style={{ maxWidth: "320px", height: "auto" }}
          />
          <p className="text-xl text-slate-600">
            Scaffolding and patterns for Laravel API/SDK integrations
          </p>
        </div>

        {/* The Core Problem */}
        <div className="bg-white rounded-2xl shadow-xl p-8 mb-8 border-2 border-slate-300">
          <h2 className="text-2xl font-bold text-slate-900 mb-6 text-center">
            The Core Problem to Solve
          </h2>

          <div className="grid md:grid-cols-2 gap-8 mb-6">
            {/* Given */}
            <div>
              <h3 className="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                <AlertCircle className="w-5 h-5 text-amber-600" />
                Given
              </h3>
              <ul className="space-y-3 text-slate-700">
                <li className="flex items-start gap-2">
                  <CheckCircle className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                  <span>
                    Modern Laravel app needs{" "}
                    <strong>5-15 external integrations</strong>
                  </span>
                </li>
                <li className="flex items-start gap-2">
                  <CheckCircle className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                  <span>
                    Mix of <strong>official SDKs</strong> (Google Ads, Stripe),{" "}
                    <strong>REST APIs</strong> (Mailchimp),{" "}
                    <strong>SOAP</strong> (legacy), <strong>MCP</strong>{" "}
                    (future)
                  </span>
                </li>
                <li className="flex items-start gap-2">
                  <CheckCircle className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                  <span>
                    Team of <strong>3-10 developers</strong> over{" "}
                    <strong>3-5 year lifespan</strong>
                  </span>
                </li>
                <li className="flex items-start gap-2">
                  <CheckCircle className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                  <span>
                    Business logic <strong>must not be coupled</strong> to
                    vendor implementation details
                  </span>
                </li>
              </ul>
            </div>

            {/* Goal */}
            <div>
              <h3 className="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                <Sparkles className="w-5 h-5 text-emerald-600" />
                Goal
              </h3>
              <p className="text-slate-700 leading-relaxed mb-4">
                <strong className="text-emerald-700">
                  Uniform interface for heterogeneous integrations.
                </strong>
              </p>
              <p className="text-slate-700 leading-relaxed mb-4">
                Provide <strong>architectural patterns and tooling</strong> so
                that all integrations look consistent from the app's
                perspective, while allowing full access to underlying APIs.
              </p>
              <div className="bg-gradient-to-r from-emerald-50 to-teal-50 border-l-4 border-emerald-500 rounded-r-lg p-4">
                <p className="text-sm font-semibold text-slate-900 mb-2">
                  This means:
                </p>
                <ul className="text-sm text-slate-700 space-y-1 ml-1 list-disc">
                  <li>
                    <strong>Consistency</strong> across SDK, REST, SOAP, MCP
                    integrations
                  </li>
                  <li>
                    <strong>Predictability</strong> in how your app interacts
                    with ANY external service
                  </li>
                  <li>
                    <strong>Testability</strong> - same mocking strategy for all
                    integrations
                  </li>
                  <li>
                    <strong>Discoverability</strong> - new dev sees Gateway
                    pattern everywhere
                  </li>
                  <li>
                    <strong>Swappability</strong> - change providers without
                    touching business logic
                  </li>
                </ul>
              </div>
            </div>
          </div>

          {/* Avoid */}
          <div className="bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 rounded-r-lg p-6">
            <h3 className="text-lg font-bold text-slate-900 mb-3 flex items-center gap-2">
              <AlertCircle className="w-5 h-5 text-red-600" />
              What This Is NOT
            </h3>
            <ul className="text-sm text-slate-700 space-y-2 ml-1 list-disc">
              <li>
                <strong>Not an "API wrapper for X"</strong> - Wrappers promise
                complete coverage but deliver 20-40%. They can't keep up with
                API evolution.
              </li>
              <li>
                <strong>Not a "unified API client"</strong> - REST is a
                philosophy, not a protocol. Universal abstraction is impossible
                without losing unique provider value.
              </li>
              <li>
                <strong>Not complete endpoint coverage</strong> - It provides
                architecture and patterns. You implement the adapters you need.
              </li>
              <li>
                <strong>Not hiding complexity</strong> - Leaky abstractions
                break under real use. We provide structure, not magic.
              </li>
              <li>
                <strong>Not code generation for its own sake</strong> -
                Generated code is only valuable if it's maintainable and fits
                your architecture.
              </li>
            </ul>
            <p className="text-xs text-slate-600 mt-4 italic">
              Even API creators struggle to maintain good SDKs. Third-party
              wrappers are doomed from the start. Plenipotentiary gives you
              patterns, not promises.
            </p>
          </div>
        </div>

        {/* Cross-cutting concerns moved below */}
        <div className="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-slate-200">
          <h3 className="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
            <Shield className="w-5 h-5 text-emerald-600" />
            Cross-Cutting Concerns (When You Need Them)
          </h3>
          <p className="text-sm text-slate-600 mb-4">
            Robust integrations need these in priority order. Some integrations
            need none of these. Financial integrations need all of them.
          </p>
          <div className="grid md:grid-cols-2 gap-3">
            <div className="flex items-start gap-2">
              <span className="text-emerald-600 font-bold text-sm">1.</span>
              <div className="text-sm text-slate-700">
                <strong>Validation</strong> - Your app's requirements enforced
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="text-emerald-600 font-bold text-sm">2.</span>
              <div className="text-sm text-slate-700">
                <strong>Idempotency</strong> - Safe retries, no duplicate
                charges
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="text-emerald-600 font-bold text-sm">3.</span>
              <div className="text-sm text-slate-700">
                <strong>Test coverage</strong> - High confidence in production
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="text-emerald-600 font-bold text-sm">4.</span>
              <div className="text-sm text-slate-700">
                <strong>Persistence</strong> - Audit trail, debugging
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="text-emerald-600 font-bold text-sm">5.</span>
              <div className="text-sm text-slate-700">
                <strong>Policy enforcement</strong> - Rate limits, budgets,
                approvals
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="text-emerald-600 font-bold text-sm">6.</span>
              <div className="text-sm text-slate-700">
                <strong>Error handling</strong> - Retries, circuit breakers
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="text-emerald-600 font-bold text-sm">7.</span>
              <div className="text-sm text-slate-700">
                <strong>Observability</strong> - Logging, metrics, tracing
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
                <h2 className="text-xl font-bold text-slate-900 m-0">TL;DR</h2>
              </div>
              <p className="text-base text-slate-700 mb-3 leading-relaxed">
                Think of it like{" "}
                <code className="bg-slate-100 px-2 py-0.5 rounded text-slate-800">
                  artisan:make
                </code>{" "}
                for third-party APIs: declare the provider, domain, context and
                resource and instantly scaffold the contracts, DTOs, gateways
                and test harness you need. You still implement the Adapter (it's
                not magic), but the code now sits in a consistent, testable,
                tool-friendly structure.
              </p>
              <p className="text-base text-slate-700 leading-relaxed mb-0">
                Flysystem-style consistency for APIs, while recognizing not
                everything should be abstracted. Flysystem works because
                filesystems share common verbs. APIs don't—REST is philosophy,
                SDKs are vendor-specific, SOAP is legacy. Plenipotentiary gives
                them all the same architectural pattern, so your app doesn't
                care what's underneath.
              </p>
            </div>
          </div>

          {/* Dictionary Definition */}
          <div className="md:col-span-1">
            <div className="bg-white rounded-2xl shadow-lg p-6 border border-slate-200 h-full">
              <h3 className="text-lg font-bold text-slate-900 mb-2">
                plenipotentiary
              </h3>
              <div className="text-sm text-slate-500 mb-4">
                /ˌplɛnɪpəˈtɛn(t)ʃ(ə)ri/
              </div>
              <p className="text-base text-slate-700 leading-relaxed mb-0">
                a person{" "}
                <code className="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded text-sm">
                  GATEWAY/ADAPTER
                </code>
                , invested with the full power of independent action on behalf
                of their government{" "}
                <code className="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-sm">
                  DOMAIN
                </code>
                , typically in a foreign country{" "}
                <code className="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-sm">
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
