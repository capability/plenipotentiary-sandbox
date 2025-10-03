import React, { useState } from "react";
import {
  Database,
  Zap,
  Wrench,
  Network,
  CheckCircle,
  AlertCircle,
  FileCode,
  Layers,
  Shield,
  Sparkles,
} from "lucide-react";

type PatternType = "crud" | "operation" | "procedure" | "rest";

interface Pattern {
  id: PatternType;
  name: string;
  tagline: string;
  when: string;
  structure: string;
  example: string;
  useCases: string[];
  features: {
    typeSafety: number;
    validation: number;
    discoverability: number;
    easeOfSetup: number;
    persistence: number;
    idempotency: number;
  };
  icon: React.ComponentType<{ size?: number; className?: string }>;
  color: string;
}

const patterns: Pattern[] = [
  {
    id: "crud",
    name: "CRUD Pattern",
    tagline: "Resource Lifecycle Management",
    when: "Managing resources with Create/Read/Update/Delete lifecycle",
    structure: `Contexts/{Context}/{Resource}/
  ├── Adapter/
  │   ├── {Resource}CrudAdapter.php
  │   ├── {Resource}Create.php
  │   ├── {Resource}Update.php
  │   └── {Resource}Delete.php
  ├── Gateway/
  │   └── {Resource}CrudGateway.php
  └── DTO/
      └── {Resource}CanonicalDTO.php`,
    example: `$campaign = CampaignCanonicalDTO::fromArray([
  'name' => 'Summer Sale',
  'budget' => 50000,
  'status' => 'ENABLED',
]);

$result = $gateway->create($campaign);`,
    useCases: [
      "Google Ads Campaigns",
      "Stripe Customers",
      "Shopify Products",
      "Xero Invoices",
    ],
    features: {
      typeSafety: 100,
      validation: 100,
      discoverability: 100,
      easeOfSetup: 60,
      persistence: 100,
      idempotency: 100,
    },
    icon: Database,
    color: "blue",
  },
  {
    id: "operation",
    name: "Operation Pattern",
    tagline: "Use Case Driven",
    when: "Non-CRUD operations like search, generate, verify, calculate",
    structure: `Contexts/Default/
  ├── Operations/
  │   └── {UseCase}/
  │       ├── {UseCase}Operation.php
  │       ├── {UseCase}Gateway.php
  │       ├── {UseCase}DTO.php
  │       └── {UseCase}Result.php
  └── Actions/
      └── {UseCase}Action.php`,
    example: `$dto = SearchItemsDTO::fromArray([
  'query' => 'laptop',
  'priceMax' => 500,
  'condition' => 'NEW',
]);

$result = $searchAction->handle($dto);`,
    useCases: [
      "eBay Browse Search",
      "OpenAI Completions",
      "Google Ads Reporting",
      "Price Calculators",
    ],
    features: {
      typeSafety: 100,
      validation: 100,
      discoverability: 100,
      easeOfSetup: 80,
      persistence: 80,
      idempotency: 100,
    },
    icon: Zap,
    color: "emerald",
  },
  {
    id: "procedure",
    name: "Procedure Pattern",
    tagline: "Simple RPC",
    when: "Quick prototypes, simple one-off operations",
    structure: `Shared/Transfer/Procedure/
  ├── {Provider}ProcedureAdapter.php
  ├── {Provider}ProcedureGateway.php
  └── {Provider}ProcedureConnector.php`,
    example: `$result = $gateway->call('searchItems', [
  'q' => 'laptop',
  'limit' => 50,
  'filter' => 'price:[..500]',
]);`,
    useCases: [
      "Admin Tools",
      "Quick Scripts",
      "Rapid Prototyping",
      "One-off Operations",
    ],
    features: {
      typeSafety: 40,
      validation: 40,
      discoverability: 40,
      easeOfSetup: 100,
      persistence: 40,
      idempotency: 40,
    },
    icon: Wrench,
    color: "orange",
  },
  {
    id: "rest",
    name: "REST Pattern",
    tagline: "Dedicated Requests",
    when: "Many endpoints, need type-safe dedicated classes",
    structure: `Requests/
  ├── SearchItemsRequest.php
  └── GetItemDetailsRequest.php

Shared/Transfer/Rest/
  ├── {Provider}RestAdapter.php
  └── {Provider}RestConnector.php`,
    example: `$request = new SearchItemsRequest(
  query: 'laptop',
  limit: 20,
  priceMax: 500
);

$result = $connector->send($request);`,
    useCases: [
      "APIs with 50+ Endpoints",
      "Complex Request Config",
      "Per-Endpoint Type Safety",
    ],
    features: {
      typeSafety: 100,
      validation: 80,
      discoverability: 80,
      easeOfSetup: 80,
      persistence: 80,
      idempotency: 80,
    },
    icon: Network,
    color: "purple",
  },
];

const FeatureBar = ({ label, value }: { label: string; value: number }) => (
  <div className="mb-3">
    <div className="flex items-center justify-between mb-1">
      <span className="text-sm font-medium text-slate-700">{label}</span>
      <span className="text-sm font-bold text-slate-900">{value}%</span>
    </div>
    <div className="h-2 bg-slate-200 rounded-full overflow-hidden">
      <div
        className={`h-full rounded-full transition-all duration-300 ${
          value >= 80
            ? "bg-gradient-to-r from-emerald-400 to-emerald-600"
            : value >= 60
              ? "bg-gradient-to-r from-amber-400 to-amber-600"
              : "bg-gradient-to-r from-red-400 to-red-600"
        }`}
        style={{ width: `${value}%` }}
      />
    </div>
  </div>
);

export default function PatternGuide() {
  const [selectedPattern, setSelectedPattern] =
    useState<PatternType>("operation");

  const scenarios = [
    { id: "search", label: "Search/Query API", recommended: "operation" },
    { id: "resource", label: "Resource Management", recommended: "crud" },
    { id: "quick", label: "Quick Script", recommended: "procedure" },
    { id: "many", label: "Many Endpoints", recommended: "rest" },
  ];

  const [scenario, setScenario] = useState<string>("search");
  const currentPattern =
    patterns.find((p) => p.id === selectedPattern) || patterns[1];
  const currentScenario = scenarios.find((s) => s.id === scenario);

  return (
    <div className="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-16 px-4">
      <div className="max-w-6xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center justify-center gap-3 mb-4">
            <Layers className="w-10 h-10 text-emerald-600" />
            <h2 className="text-3xl font-bold text-slate-900 m-0">
              Patterns
            </h2>
          </div>
          <p className="text-lg text-slate-600 max-w-3xl mx-auto">
            Five proven patterns for different integration styles. Pick the one that matches your API, not a one-size-fits-all wrapper.
          </p>
        </div>

        {/* Scenario Selector */}
        <div className="bg-white rounded-2xl shadow-lg overflow-hidden mb-6 border border-slate-200">
          <div className="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 px-6 py-4 text-white">
            <h3 className="text-lg font-bold m-0">What are you building?</h3>
          </div>
          <div className="p-6">
            <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
              {scenarios.map((s) => (
                <button
                  key={s.id}
                  onClick={() => {
                    setScenario(s.id);
                    setSelectedPattern(s.recommended as PatternType);
                  }}
                  className={`px-4 py-3 rounded-lg font-semibold text-sm transition-all duration-200 ${
                    scenario === s.id
                      ? "bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md"
                      : "bg-slate-100 text-slate-700 hover:bg-slate-200"
                  }`}
                >
                  {s.label}
                </button>
              ))}
            </div>
            {currentScenario && (
              <div className="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
                <p className="m-0 text-sm text-slate-700">
                  <strong className="text-emerald-900">Recommended:</strong>{" "}
                  <span className="text-emerald-700">
                    {
                      patterns.find((p) => p.id === currentScenario.recommended)
                        ?.name
                    }
                  </span>
                </p>
              </div>
            )}
          </div>
        </div>

        {/* Pattern Tabs */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          {patterns.map((pattern) => {
            const Icon = pattern.icon;
            const isActive = selectedPattern === pattern.id;
            return (
              <button
                key={pattern.id}
                onClick={() => setSelectedPattern(pattern.id)}
                className={`p-4 rounded-2xl transition-all duration-300 text-left border-2 ${
                  isActive
                    ? "bg-white shadow-lg border-emerald-500"
                    : "bg-white border-slate-200 hover:border-emerald-300 hover:shadow-md"
                }`}
              >
                <div className="flex items-center gap-3 mb-2">
                  <div
                    className={`w-10 h-10 rounded-lg flex items-center justify-center ${
                      isActive
                        ? "bg-gradient-to-br from-emerald-400 to-teal-600"
                        : "bg-slate-100"
                    }`}
                  >
                    <Icon
                      className={`w-5 h-5 ${isActive ? "text-white" : "text-slate-600"}`}
                    />
                  </div>
                  <div className="font-bold text-base text-slate-900">{pattern.name}</div>
                </div>
                <div className="text-sm text-slate-600">{pattern.tagline}</div>
              </button>
            );
          })}
        </div>

        {/* Pattern Details */}
        <div className="bg-white rounded-2xl shadow-lg overflow-hidden border border-slate-200">
          <div className="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 px-6 py-4 text-white">
            <h3 className="text-xl font-bold m-0 mb-2">
              {currentPattern.name}
            </h3>
            <p className="text-sm text-emerald-100 m-0">
              <strong>Use when:</strong> {currentPattern.when}
            </p>
          </div>

          <div className="p-8">
            <div className="grid lg:grid-cols-2 gap-8">
              {/* Left Column - Structure & Example */}
              <div>
                <div className="flex items-center gap-2 mb-3">
                  <Layers className="w-5 h-5 text-emerald-600" />
                  <h4 className="text-lg font-bold text-slate-900 m-0">
                    Structure
                  </h4>
                </div>
                <div className="bg-slate-900 rounded-2xl overflow-hidden shadow-lg mb-6">
                  <div className="flex items-center gap-2 px-5 py-3 bg-slate-800 border-b border-slate-700">
                    <div className="flex gap-2">
                      <div className="w-3 h-3 rounded-full bg-red-500"></div>
                      <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                      <div className="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                  </div>
                  <pre className="p-5 overflow-x-auto text-sm leading-relaxed m-0">
                    <code className="text-slate-300 font-mono">
                      {currentPattern.structure}
                    </code>
                  </pre>
                </div>

                <div className="flex items-center gap-2 mb-3">
                  <FileCode className="w-5 h-5 text-emerald-600" />
                  <h4 className="text-lg font-bold text-slate-900 m-0">
                    Developer Usage
                  </h4>
                </div>
                <div className="bg-slate-900 rounded-2xl overflow-hidden shadow-lg">
                  <div className="flex items-center gap-2 px-5 py-3 bg-slate-800 border-b border-slate-700">
                    <div className="flex gap-2">
                      <div className="w-3 h-3 rounded-full bg-red-500"></div>
                      <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                      <div className="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <span className="text-xs text-slate-400 ml-3 font-mono">
                      example.php
                    </span>
                  </div>
                  <pre className="p-5 overflow-x-auto text-sm leading-relaxed m-0">
                    <code className="text-slate-300 font-mono">
                      {currentPattern.example}
                    </code>
                  </pre>
                </div>
              </div>

              {/* Right Column - Features & Use Cases */}
              <div>
                <div className="flex items-center gap-2 mb-3">
                  <Shield className="w-5 h-5 text-emerald-600" />
                  <h4 className="text-lg font-bold text-slate-900 m-0">
                    Feature Coverage
                  </h4>
                </div>
                <div className="bg-slate-50 rounded-2xl p-5 mb-6 border border-slate-200">
                  <FeatureBar
                    label="Type Safety"
                    value={currentPattern.features.typeSafety}
                  />
                  <FeatureBar
                    label="Validation"
                    value={currentPattern.features.validation}
                  />
                  <FeatureBar
                    label="Discoverability"
                    value={currentPattern.features.discoverability}
                  />
                  <FeatureBar
                    label="Ease of Setup"
                    value={currentPattern.features.easeOfSetup}
                  />
                  <FeatureBar
                    label="Persistence"
                    value={currentPattern.features.persistence}
                  />
                  <FeatureBar
                    label="Idempotency"
                    value={currentPattern.features.idempotency}
                  />
                </div>

                <div className="flex items-center gap-2 mb-3">
                  <Sparkles className="w-5 h-5 text-emerald-600" />
                  <h4 className="text-lg font-bold text-slate-900 m-0">
                    Real-World Examples
                  </h4>
                </div>
                <div className="space-y-2">
                  {currentPattern.useCases.map((useCase, idx) => (
                    <div
                      key={idx}
                      className="flex items-start gap-2 p-3 bg-emerald-50 rounded-lg border border-emerald-200"
                    >
                      <CheckCircle className="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                      <span className="text-slate-800 font-medium">
                        {useCase}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  );
}
