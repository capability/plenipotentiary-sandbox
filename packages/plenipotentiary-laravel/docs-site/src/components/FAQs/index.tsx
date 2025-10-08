import React from "react";
import { HelpCircle, Target, Rocket, Layers, Network, Sparkles, CheckCircle } from "lucide-react";

interface FAQ {
  question: string;
  answer: React.ReactNode;
}

const faqs: FAQ[] = [
  {
    question: "Can't all this be done in Saloon?",
    answer: (
      <div>
        <p className="text-base mb-4">
          Saloon is a best-in-class HTTP transport layer with useful plugins. Plenipotentiary focuses on the Gateway pattern and the stability that brings, using Saloon for its proven strengths.
        </p>
        <div className="grid md:grid-cols-2 gap-6">
          <div>
            <div className="flex items-center gap-2 mb-3">
              <div className="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                <Network className="w-4 h-4 text-slate-600" />
              </div>
              <strong className="text-base text-slate-900">Saloon gives you:</strong>
            </div>
            <ul className="space-y-2 m-0 list-none pl-0">
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-slate-400 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">HTTP client (Connector/Request classes)</span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-slate-400 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">Authentication (OAuth2, token auth)</span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-slate-400 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">Middleware, retries, rate limiting</span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-slate-400 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">Pagination, caching, mocking</span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-slate-400 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">Recording requests for tests</span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-slate-400 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">Request pools & concurrency</span>
              </li>
            </ul>
          </div>
          <div>
            <div className="flex items-center gap-2 mb-3">
              <div className="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <Sparkles className="w-4 h-4 text-emerald-600" />
              </div>
              <strong className="text-base text-slate-900">Plenipotentiary adds:</strong>
            </div>
            <ul className="space-y-2 m-0 list-none pl-0">
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-emerald-500 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">
                  <strong>Patterns</strong> - CRUD, Operation, Procedure, REST, MCP Proxy (niche)
                </span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-emerald-500 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">
                  <strong>Layers</strong> - Gateway (stable) vs Adapter (provider-specific)
                </span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-emerald-500 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">
                  <strong>Contracts</strong> - CanonicalDTO, Result monad, Selector
                </span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-emerald-500 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">
                  <strong>Scaffolding</strong> - Artisan commands for rapid setup
                </span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-emerald-500 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">
                  <strong>Integration</strong> - Actions, Jobs, Commands, Tests
                </span>
              </li>
              <li className="flex items-start gap-2">
                <CheckCircle className="w-3.5 h-3.5 text-emerald-500 mt-1 flex-shrink-0" />
                <span className="text-sm text-slate-700">
                  <strong>Cross-cutting</strong> - Idempotency hints, error mapping policies
                </span>
              </li>
            </ul>
          </div>
        </div>
        <div className="mt-4 p-3 bg-cyan-50 border-l-4 border-cyan-500 rounded-r-lg">
          <p className="text-sm text-slate-700 italic m-0">
            Saloon is the HTTP transport layer. Plenipotentiary is the integration architecture layer with patterns, scaffolding and domain contracts. They work together—Plenipotentiary uses Saloon underneath.
          </p>
        </div>
      </div>
    ),
  },
  {
    question: "Why Plenipotentiary? I can't even say it, never mind spell it.",
    answer: (
      <p className="text-base text-slate-700 m-0">
        Ambassador, Envoy, Emissary, Delegate, Proxy... all the good names are gone. Plenipotentiary captured what it does.
        Count yourself lucky, I had scattered terms like ForeignState and Ministry throughout an earlier iteration until I
        accepted that metaphors don't really belong in code. Also, who's going to clash with a Pleni namespace?
      </p>
    ),
  },
  {
    question: "Oh no... it's an API wrapper, isn't it?",
    answer: (
      <p className="text-base text-slate-700 m-0">
        Nope. It's an <em>orchestration + anti-corruption layer</em>. You still write Adapters; we add guardrails (retries, logging, idempotency, error mapping).
      </p>
    ),
  },
  {
    question: "But this is just what you do when you integrate an API anyway, isn't it?",
    answer: (
      <p className="text-base text-slate-700 m-0">
        Exactly! But it's now repeatable, predictable, and testable instead of five ad-hoc services with five retry strategies.
      </p>
    ),
  },
  {
    question: "I could achieve the same thing with a few files in my service layer!",
    answer: (
      <p className="text-base text-slate-700 m-0">
        You can. Then again for another service six months later… implemented differently because the new API has its own quirks.
        By splitting Gateway and Adapter, I keep SDK churn isolated, make the integration testable with mocks, and guarantee things
        like idempotency and error mapping are always applied. Pleni saves you from your future self.
      </p>
    ),
  },
  {
    question: "What? So you want me to learn your approach AND a new API?",
    answer: (
      <p className="text-base text-slate-700 m-0">
        ~10 minutes to learn Gateway ↔ Adapter/Operations. After that, you're just writing the same code you'd normally drop into
        a service class... but in the Adapter, where it's isolated. Pleni isn't an SDK wrapper! You still need to know the provider API.
        The difference is you only expose what you need, not the entire SDK surface. Over time the community can share common Adapters
        for basic ops, but the goal is clean contracts and boundaries, not hiding APIs behind another API.
      </p>
    ),
  },
  {
    question: "Er… have you ever heard of Saloon / Guzzle / Laravel HTTP?",
    answer: (
      <p className="text-base text-slate-700 m-0">
        We love them. Use them in your Adapter if you like. Pleni isn't an HTTP client; it's the structure around your integration.
        It doesn't compete with Saloon, it gives Saloon a predictable home.
      </p>
    ),
  },
  {
    question: "Er… have you ever heard of ETL / Pipes / Workflows?",
    answer: (
      <p className="text-base text-slate-700 m-0">
        Yes. They're great... when you need a full pipeline orchestrator. Pleni isn't that. It's for exposing just the slice of a
        big API you need, fast, with guardrails you'll want when things hit production. Use ETL for data pipelines; use Pleni for
        integrations inside your app.
      </p>
    ),
  },
  {
    question: "Okay. I understand provider, domain, resource... but what's a context?",
    answer: (
      <div>
        <p className="text-base text-slate-700 mb-3">
          A context is just a way of saying "this thing works slightly differently depending on how you use it".
        </p>
        <p className="text-base text-slate-700 mb-3">
          Using Google Ads as an example. There are lots of{" "}
          <a href="https://developers.google.com/google-ads/api/docs/campaigns/overview" className="text-emerald-600 hover:text-emerald-700 underline">
            different campaign types
          </a>{" "}
          - Search, Display, Shopping, Performance Max, App, Local Services, and more. They all look like "campaigns", but each has
          its own rules and setup. Dig deeper and you'll find Ads and Ad Groups behave differently in different campaigns.
        </p>
        <p className="text-base text-slate-700 m-0">
          In Plenipotentiary, you can model your "Search" marketing strategy (i.e. text ads) as Campaigns, Ad Groups, Ad Group Criterion,
          and Ads all in one context "Search". Create a new context for your PMAX or Shopping campaigns and you can have different rules,
          DTOs, and even adapters if you need them. But they all share the same stable Gateway contract, so your app code stays clean.
          Or don't, the package doesn't impose any structure.
        </p>
      </div>
    ),
  },
  {
    question: "But... it IS an API wrapper, isn't it?",
    answer: (
      <p className="text-base text-slate-700 m-0">
        Okay Yes... in the sense that we all wrap code around APIs/SDKs... but this wrapper adds clean contracts, testability,
        idempotency, error handling, queuing, extensibility, and the ability to swap out adapters... which I think is a good thing.
      </p>
    ),
  },
];

export default function FAQs() {
  return (
    <div className="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-16 px-4">
      <div className="max-w-6xl mx-auto">
        {/* FAQ Section */}
        <div className="text-center mb-8">
          <div className="inline-flex items-center justify-center gap-3 mb-4">
            <HelpCircle className="w-10 h-10 text-emerald-600" />
            <h2 className="text-3xl font-bold text-slate-900 m-0">
              FAQs
            </h2>
          </div>
          <p className="text-lg text-slate-600 max-w-3xl mx-auto">
            Things you were about to comment
          </p>
        </div>

        <div className="space-y-6">
          {faqs.map((faq, index) => (
            <div
              key={index}
              className="bg-white rounded-2xl shadow-lg p-6 border border-slate-200"
            >
              <h3 className="text-lg font-bold text-slate-900 mb-3">
                {faq.question}
              </h3>
              <div className="text-sm text-slate-700 leading-relaxed">
                {faq.answer}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
