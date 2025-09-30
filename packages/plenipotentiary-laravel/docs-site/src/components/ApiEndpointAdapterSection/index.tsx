import React from 'react';
import Heading from '@theme/Heading';
import CodeBlock from '@theme/CodeBlock';
import styles from './styles.module.css';

/**
 * ApiEndpointAdapterSection
 *
 * Homepage section explaining why ApiEndpointAdapterContract exists
 * (beyond CRUD) and what guardrails it provides.
 * Layout: container → row → single col (12).
 */
export default function ApiEndpointAdapterSection() {
  const iface = `interface ApiEndpointAdapterContract
{
    /**
     * Call any provider operation with a payload and optional call options
     * (e.g. field masks, headers, retry hints). Returns a Result wrapper.
     */
    public function call(string $operation, array $payload = [], array $options = []): Result;

    /**
     * Validate a payload for a named operation without making a provider call.
     * Useful for local checks and dry-runs in CI.
     */
    public function validate(string $operation, array $payload = []): Result;
}`;

  return (
    <section className={styles.bg}>
      <div className="container">
        <div className="row">
          <div className="col col--12">
            <Heading as="h2" className="margin-bottom--md">ApiEndpointAdapterContract: Beyond CRUD Operations</Heading>

            <Heading as="h3" className="margin-bottom--sm">CRUD as Universal Foundation</Heading>
            <p>
              CRUD operations (Create, Read, Update, Delete) are universal across many APIs... like filesystem
              operations, they can be abstracted because they rarely change. The
              <code> ApiCrudAdapterContract</code> provides this solid foundation for core operations that a
              provider supports.
            </p>

            <Heading as="h3" className="margin-top--lg margin-bottom--sm">The Reality: APIs Go Much Further Than CRUD</Heading>
            <p>Modern APIs extend far beyond basic CRUD operations. They include:</p>
            <ul>
              <li><strong>Domain-specific operations:</strong> <code>searchItems</code>, <code>createCompletion</code>, <code>generateImage</code></li>
              <li><strong>Complex workflows:</strong> <code>cancelFineTune</code>, <code>searchByImage</code>, <code>getItemAspectsForCategory</code></li>
              <li><strong>Provider-specific capabilities:</strong> every API exposes unique functionality</li>
            </ul>

            <Heading as="h3" className="margin-top--lg margin-bottom--sm">ApiEndpointAdapterContract: Loose Abstraction with Guardrails</Heading>
            <p>
              The <code>ApiEndpointAdapterContract</code> offers a loose abstraction that lets you reach any part of
              an API while keeping essential guardrails for production integrations.
            </p>
            <CodeBlock language="php">{iface}</CodeBlock>

            <Heading as="h3" className="margin-top--lg margin-bottom--sm">What You Get: All the Benefits, None of the Constraints</Heading>
            <ul>
              <li>✅ Logging and monitoring for all operations</li>
              <li>✅ Idempotency handling for state-changing operations</li>
              <li>✅ Error mapping and handling across providers</li>
              <li>✅ Validation before API calls</li>
              <li>✅ Provider isolation (SDKs stay in adapters)</li>
              <li>✅ Gateway/Adapter pattern consistency</li>
            </ul>

            <Heading as="h3" className="margin-top--lg margin-bottom--sm">What You Don't Get: Artificial Constraints</Heading>
            <ul>
              <li>❌ No forced translation of operation names</li>
              <li>❌ No rigid verb requirements</li>
              <li>❌ No one-size-fits-all "universal API" that leaks or breaks semantics</li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  );
}
