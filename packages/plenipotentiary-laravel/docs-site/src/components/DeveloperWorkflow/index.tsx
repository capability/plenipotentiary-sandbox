import React from "react";
import Heading from "@theme/Heading";

/**
 * Developer Workflow (Docusaurus component)
 * - Single 12-column layout using Infima's container/row/col classes
 * - Emphasizes "Auth first", then "Start with the SDK", with no-magic messaging
 */
export default function DeveloperWorkflow(): JSX.Element {
  const steps: { title: string; body: React.ReactNode }[] = [
    {
      title: "Auth, simply (built-in strategies)",
      body: (
        <>
          Use env-driven auth strategies to obtain a real, authenticated client
          quickly. Prove calls with real credentials (sandbox/validateOnly when
          available); swap in a mocked client for unit tests.
        </>
      ),
    },
    {
      title: "Start with the API or SDK (understanding over abstraction)",
      body: (
        <>
          Open one file, one operation. Adapt (almost paste) the provider SDK
          example and make it run. Map the snippet to your business/application
          use case and identify the <em>minimum</em> data you truly need with a
          flat mock array... all in one easy-to-understand place.
          Plenipotentiary promotes understanding, not magic or over abstraction.
        </>
      ),
    },
    {
      title: "Define your INPUT_SPEC",
      body: (
        <>
          Codify those required fields as the operation's{" "}
          <code>INPUT_SPEC</code>. This is your explicit contract with the
          API... visible, auditable, and owned by you.
        </>
      ),
    },
    {
      title: "Stay in one place until it's green",
      body: (
        <>
          Keep everything in the adapter operation... understand the data you
          need, use the data to build the request <code>requestMapper()</code>,
          map the response in the other direction <code>responseMapper()</code>
          ... until your unit test for <code>perform()</code> is green (covers
          success, invalid input, and mapped errors). Once the test is green,
          you've genuinely understood the API vs your use case and you are ready
          for the next step.
        </>
      ),
    },
    {
      title: "Run through the Gateway - Your Domain, Predictable",
      body: (
        <>
          Call the Gateway. It will initially fail... but the error payload will
          show you the CanonicalDTO and Factory that match the{" "}
          <code>INPUT_SPEC</code> you just defined. This is the stable,
          provider-agnostic port into your app. The Gateway turns operation
          outcomes into a uniform <code>Result</code> shape (ok/err/invalid),
          normalizes provider errors into domain errors and enables idempotency
          and observability.
        </>
      ),
    },
    {
      title: "Scaffold appears, to your spec",
      body: (
        <>
          Generate/paste the DTO and Factory. They aren't guesses... they're
          built directly from the spec you wrote while working with the SDK.
        </>
      ),
    },
    {
      title: "Robustness comes online",
      body: (
        <>
          With the Gateway boundary in play you get:
          <ul>
            <li>
              Predictable validation (from your <code>INPUT_SPEC</code>)
            </li>
            <li>Idempotency and safe retries</li>
            <li>Clean domain error mapping</li>
            <li>Safe logging/redaction</li>
            <li>Queueing, scheduling, cron integration</li>
          </ul>
        </>
      ),
    },
  ];

  return (
    <section className="margin-vert--lg">
      <div className="container">
        <div className="row">
          <div className="col col--12">
            <Heading as="h2" className="margin-bottom--sm">
              Developer Workflow
            </Heading>
            <p className="margin-bottom--lg" style={{ opacity: 0.9 }}>
              Learn the SDK first, codify your contract, then let the tooling
              scaffold itself to your spec. No magic... just leverage built on
              understanding.
            </p>

            <ol style={{ listStyle: "none", padding: 0, margin: 0 }}>
              {steps.map((s, i) => (
                <li
                  key={i}
                  className="card margin-bottom--md"
                  style={{ borderRadius: "16px" }}
                >
                  <div
                    className="card__header"
                    style={{
                      display: "flex",
                      alignItems: "center",
                      gap: "0.5rem",
                    }}
                  >
                    <span
                      className="badge badge--primary"
                      style={{
                        width: 28,
                        height: 28,
                        display: "inline-flex",
                        alignItems: "center",
                        justifyContent: "center",
                      }}
                    >
                      {i + 1}
                    </span>
                    <Heading as="h3" className="margin-vert--none">
                      {s.title}
                    </Heading>
                  </div>
                  <div className="card__body">
                    <div style={{ lineHeight: 1.6 }}>{s.body}</div>
                  </div>
                </li>
              ))}
            </ol>

            <div className="alert alert--info" style={{ marginTop: "1rem" }}>
              <strong>TL;DR</strong>: Understand the SDK, write your{" "}
              <code>INPUT_SPEC</code>, then generate the DTO/Factory from your
              own spec. Tooling follows your understanding.
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
