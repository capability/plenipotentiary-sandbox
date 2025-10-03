import React, { useState, useEffect } from "react";
import useBaseUrl from "@docusaurus/useBaseUrl";
import { BookOpen, CheckCircle, Layers, Shield, AlertCircle, Network, Sparkles, Terminal } from "lucide-react";

interface Example {
  title: string;
  command: string;
}

const examples: Example[] = [
  {
    title: "Stripe Payment Gateway",
    command: "php artisan pleni:make:crud \\\n  --provider=Stripe \\\n  --domain=Billing \\\n  --resource=Customer \\\n  --with-actions \\\n  --with-tests",
  },
  {
    title: "eBay Product Search",
    command: "php artisan pleni:make:operation \\\n  --provider=eBay \\\n  --domain=Browse \\\n  --resource=SearchItems \\\n  --with-controller \\\n  --with-tests",
  },
  {
    title: "GitHub API Integration",
    command: "php artisan pleni:make:rest \\\n  --provider=GitHub \\\n  --domain=API \\\n  --resource=Repositories \\\n  --with-requests \\\n  --with-tests",
  },
  {
    title: "Internal Admin Tool",
    command: "php artisan pleni:make:procedure \\\n  --provider=InternalAPI \\\n  --domain=Admin \\\n  --resource=SendAlert \\\n  --with-commands",
  },
  {
    title: "AI Agent with MCP",
    command: "php artisan pleni:make:mcp \\\n  --provider=MCP \\\n  --domain=CustomerAnalytics \\\n  --resource=AnalyzeCustomer \\\n  --with-actions \\\n  --with-jobs \\\n  --with-tests",
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
        {/* Logo and Key Points */}
        <div className="grid md:grid-cols-2 gap-8 mb-8 items-center">
          {/* Left: Logo */}
          <div className="text-center">
            <img
              src={logoUrl}
              alt="Plenipotentiary"
              className="mx-auto mb-4"
              style={{ maxWidth: "320px", height: "auto" }}
            />
            <p className="text-xl text-slate-600">
              Scaffolding and patterns for Laravel API integrations
            </p>
          </div>

          {/* Right: Key Points */}
          <div className="grid gap-4">
            <div className="bg-white rounded-2xl shadow-lg p-5 border border-slate-200">
              <div className="flex items-start gap-3">
                <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center flex-shrink-0">
                  <BookOpen className="w-6 h-6 text-white" />
                </div>
                <div>
                  <h4 className="font-bold mb-1 text-base text-slate-900">Understand First, Don't Abstract to Magic</h4>
                  <p className="text-sm text-slate-700 leading-relaxed">
                    Learn the real API first. Plenipotentiary provides patterns and scaffolding,
                    not a leaky abstraction that hides complexity.
                  </p>
                </div>
              </div>
            </div>
            <div className="bg-white rounded-2xl shadow-lg p-5 border border-slate-200">
              <div className="flex items-start gap-3">
                <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center flex-shrink-0">
                  <Shield className="w-6 h-6 text-white" />
                </div>
                <div>
                  <h4 className="font-bold mb-1 text-base text-slate-900">Gateway Pattern for Stability</h4>
                  <p className="text-sm text-slate-700 leading-relaxed">
                    Vendor APIs change constantly. Gateway stays stable, Adapter absorbs the churn.
                    Your app talks to consistent contracts.
                  </p>
                </div>
              </div>
            </div>
            <div className="bg-white rounded-2xl shadow-lg p-5 border border-slate-200">
              <div className="flex items-start gap-3">
                <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center flex-shrink-0">
                  <Layers className="w-6 h-6 text-white" />
                </div>
                <div>
                  <h4 className="font-bold mb-1 text-base text-slate-900">Proven Patterns & Tools</h4>
                  <p className="text-sm text-slate-700 leading-relaxed">
                    Five battle-tested patterns (CRUD, Operation, REST, Procedure, MCP) built on
                    Saloon, Laravel queues, and native infrastructure.
                  </p>
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
                <h2 className="text-xl font-bold text-slate-900 m-0">TL;DR</h2>
              </div>
              <p className="text-base text-slate-700 mb-3 leading-relaxed">
                Think of it like <code className="bg-slate-100 px-2 py-0.5 rounded text-slate-800">artisan:make</code> for third-party APIs: declare the provider, domain, context and resource and instantly scaffold the contracts, DTOs, gateways and test harness you need. You still implement the Adapter (it's not magic), but the code now sits in a consistent, testable, tool-friendly structure.
              </p>
              <p className="text-base text-slate-700 leading-relaxed mb-0">
                Flysystem-style consistency for APIs, while recognizing not everything should be abstracted. Packages like Flysystem work because filesystems expose a timeless, minimal set of verbs that haven't changed in decades. APIs are different—they evolve, deprecate and fragment. Accept that churn is unavoidable but still provide guardrails and tools.
              </p>
            </div>
          </div>

          {/* Dictionary Definition */}
          <div className="md:col-span-1">
            <div className="bg-white rounded-2xl shadow-lg p-6 border border-slate-200 h-full">
              <h3 className="text-lg font-bold text-slate-900 mb-2">plenipotentiary</h3>
              <div className="text-sm text-slate-500 mb-4">/ˌplɛnɪpəˈtɛn(t)ʃ(ə)ri/</div>
              <p className="text-base text-slate-700 leading-relaxed mb-0">
                a person <code className="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded text-sm">GATEWAY/ADAPTER</code>, invested with the full power of independent action on behalf of their government <code className="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-sm">DOMAIN</code>, typically in a foreign country <code className="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-sm">API_PROVIDER</code>.
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
                  <h3 className="text-lg font-bold text-slate-900 m-0">Quick Start Examples</h3>
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
                      <div className="font-semibold text-sm">{example.title}</div>
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
                  <span className="text-xs text-slate-400 font-mono">terminal</span>
                </div>
                <div className="p-6 min-h-[200px] flex items-center">
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
