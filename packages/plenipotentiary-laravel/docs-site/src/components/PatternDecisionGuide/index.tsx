import React, { useState } from "react";
import CodeBlock from "@theme/CodeBlock";
import Heading from "@theme/Heading";
import clsx from "clsx";
import styles from "./styles.module.css";

type PatternType = "crud" | "operation" | "procedure" | "rest" | null;

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
  },
];

const FeatureBar = ({ label, value }: { label: string; value: number }) => (
  <div className={styles.featureBar}>
    <div className={styles.featureLabel}>{label}</div>
    <div className={styles.featureBarTrack}>
      <div
        className={styles.featureBarFill}
        style={{
          width: `${value}%`,
          backgroundColor:
            value >= 80
              ? "var(--ifm-color-success)"
              : value >= 60
              ? "var(--ifm-color-warning)"
              : "var(--ifm-color-danger)",
        }}
      />
    </div>
    <div className={styles.featureValue}>{value}%</div>
  </div>
);

export default function PatternDecisionGuide(): JSX.Element {
  const [selectedPattern, setSelectedPattern] =
    useState<PatternType>("operation");
  const [scenario, setScenario] = useState<string>("search");

  const scenarios = [
    { id: "search", label: "🔍 Search/Query API", recommended: "operation" },
    { id: "resource", label: "📦 Resource Management", recommended: "crud" },
    { id: "quick", label: "⚡ Quick Script", recommended: "procedure" },
    { id: "many", label: "🔗 Many Endpoints", recommended: "rest" },
  ];

  const currentPattern =
    patterns.find((p) => p.id === selectedPattern) || patterns[1];
  const currentScenario = scenarios.find((s) => s.id === scenario);

  return (
    <section className={styles.patternSection}>
      <div className="container">
        <div className="row">
          <div className="col col--12">
            <header className="margin-bottom--lg text--center">
              <Heading as="h2" className="margin-bottom--sm">
                Not Just Another Saloon Wrapper
              </Heading>
              <p className={styles.subtitle}>
                Plenipotentiary provides <strong>opinionated patterns</strong>{" "}
                for different API types. Choose the right abstraction for your
                use case, not a one-size-fits-all wrapper.
              </p>
            </header>

            {/* Interactive Scenario Selector */}
            <div className={clsx("card", styles.scenarioCard)}>
              <div className="card__header">
                <Heading as="h3">What are you building?</Heading>
              </div>
              <div className="card__body">
                <div className={styles.scenarioGrid}>
                  {scenarios.map((s) => (
                    <button
                      key={s.id}
                      className={clsx(
                        "button",
                        styles.scenarioButton,
                        scenario === s.id && styles.scenarioButtonActive
                      )}
                      onClick={() => {
                        setScenario(s.id);
                        setSelectedPattern(s.recommended as PatternType);
                      }}
                    >
                      {s.label}
                    </button>
                  ))}
                </div>

                {currentScenario && (
                  <div
                    className={clsx(
                      "alert",
                      "alert--success",
                      styles.recommendation
                    )}
                  >
                    <strong>Recommended:</strong>{" "}
                    {
                      patterns.find((p) => p.id === currentScenario.recommended)
                        ?.name
                    }
                  </div>
                )}
              </div>
            </div>

            {/* Pattern Selector */}
            <div className={styles.patternTabs}>
              {patterns.map((pattern) => (
                <button
                  key={pattern.id}
                  className={clsx(
                    styles.patternTab,
                    selectedPattern === pattern.id && styles.patternTabActive
                  )}
                  onClick={() => setSelectedPattern(pattern.id)}
                >
                  <div className={styles.patternTabName}>{pattern.name}</div>
                  <div className={styles.patternTabTagline}>
                    {pattern.tagline}
                  </div>
                </button>
              ))}
            </div>

            {/* Pattern Details */}
            <div className={clsx("card", styles.patternCard)}>
              <div className="card__header">
                <Heading as="h3">{currentPattern.name}</Heading>
                <p className={styles.patternWhen}>
                  <strong>Use when:</strong> {currentPattern.when}
                </p>
              </div>

              <div className="card__body">
                <div className="row">
                  {/* Left Column - Structure & Example */}
                  <div className="col col--6">
                    <Heading as="h4" className="margin-bottom--sm">
                      Structure
                    </Heading>
                    <CodeBlock language="text">
                      {currentPattern.structure}
                    </CodeBlock>

                    <Heading
                      as="h4"
                      className="margin-top--md margin-bottom--sm"
                    >
                      Developer Usage
                    </Heading>
                    <CodeBlock language="php">
                      {currentPattern.example}
                    </CodeBlock>
                  </div>

                  {/* Right Column - Features & Use Cases */}
                  <div className="col col--6">
                    <Heading as="h4" className="margin-bottom--sm">
                      Feature Coverage
                    </Heading>
                    <div className={styles.featuresContainer}>
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

                    <Heading
                      as="h4"
                      className="margin-top--md margin-bottom--sm"
                    >
                      Real-World Examples
                    </Heading>
                    <ul className={styles.useCaseList}>
                      {currentPattern.useCases.map((useCase, idx) => (
                        <li key={idx} className={styles.useCase}>
                          {useCase}
                        </li>
                      ))}
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            {/* Key Differentiator */}
            <div>
              <Heading as="h4" className="margin-bottom--sm">
                Why Not Just Use Saloon?
              </Heading>
              <div className="row">
                <div className="col col--6">
                  <strong>Saloon gives you:</strong>
                  <ul>
                    <li>HTTP client abstraction</li>
                    <li>Request/Response handling</li>
                    <li>Authentication strategies</li>
                  </ul>
                </div>
                <div className="col col--6">
                  <strong>Plenipotentiary adds:</strong>
                  <ul>
                    <li>
                      <strong>Patterns</strong> - CRUD, Operation, Procedure,
                      REST
                    </li>
                    <li>
                      <strong>Layers</strong> - Gateway (stable) vs Adapter
                      (provider-specific)
                    </li>
                    <li>
                      <strong>Contracts</strong> - CanonicalDTOs, Result monad,
                      INPUT_SPEC
                    </li>
                    <li>
                      <strong>Integration</strong> - Laravel Actions, Jobs,
                      Commands
                    </li>
                    <li>
                      <strong>Cross-cutting</strong> - Idempotency, logging,
                      error mapping
                    </li>
                  </ul>
                </div>
              </div>
              <p className="margin-top--sm margin-bottom--none">
                <em>
                  Saloon is the transport layer. Plenipotentiary is the
                  integration architecture layer that uses Saloon (or SDKs)
                  underneath.
                </em>
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
