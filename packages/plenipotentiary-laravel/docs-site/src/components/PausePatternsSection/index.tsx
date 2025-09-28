import React from 'react';
import CodeBlock from '@theme/CodeBlock';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

export default function PausePatternsSection() {
  const approaches = [
    {
      title: '1) Explicit - build the DTO in the command, call update()',
      code: `// Pleni/Google/Ads/Contexts/Search/Campaign/Command/PauseCampaignCommand.php
public function handle(ApiCrudGatewayContract $gateway): int
{
    $id = (string) $this->argument('id');

    $dto = CampaignCanonicalDTO::fromArray([
        'identifier' => $id,
        'status'     => 'PAUSED',
    ]);

    return $this->renderResult(
        $gateway->update($dto, (bool) $this->option('validate-only'))
    );
}`,
      pros: [
        'Maximum clarity; zero magic.',
        'Easiest to unit test (pure DTO in → out).',
        'No coupling to Eloquent or storage.'
      ],
      cons: [
        'More boilerplate in each command.',
        'If identifier building is non-trivial, you may repeat it unless extracted.'
      ],
    },
    {
      title: '2) Convenient - pass an eloquent model (if used) into a DTO builder, then update()',
      code: `public function handle(ApiCrudGatewayContract $gateway): int
{
    $campaignModel = CampaignModel::query()->findOrFail($this->argument('id'));

    $dto = CampaignCanonicalDTO::fromModel($campaignModel)->mutate(['status' => 'PAUSED']);

    return $this->renderResult(
        $gateway->update($dto, (bool) $this->option('validate-only'))
    );
}`,
      pros: [
        'Less typing; identifier mapping lives in one place.',
        'Consistent enrichment (customer/account IDs) if the builder knows them.'
      ],
      cons: [
        'Tighter coupling (to Eloquent or selector type).',
        'Risk of over-hydrating DTO for masked updates if builder includes extra fields.',
        'Tests may need fixtures/fakes for the model or builder.'
      ],
    },
    {
      title: '3) Abstracted - call a Laravel Action (use‑case), keep the command thin',
      code: `public function handle(PauseCampaignAction $action): int
{
    $id = (string) $this->argument('id');

    // Selector is a minimal "address object" used to point at a remote entity so you 
    // can read/delete, perform small state changes (e.g. pause), or generate idempotency fingerprints.
    // Not a full DTO: just enough to resolve and target the resource.

    return $this->renderResult(
        $action->handle(
            CampaignSelector::fromId($id),
            (bool) $this->option('validate-only')
        )
    );
}`,
      pros: [
        'Command becomes a one‑liner; business logic is reusable (CLI, HTTP, jobs).',
        'Action owns policy (idempotency, short‑circuit, metrics).',
        'Best for a growing library of composable operations.'
      ],
      cons: [
        'Extra indirection; need to open the Action to see the flow.',
        'Slightly more upfront wiring (DI, interfaces).'
      ],
    },
    {
      title: '4) Bonus - queue an idempotent Job (fire‑and‑forget)',
      code: `public function handle(): int
{
    PauseCampaignJob::dispatch(
        CampaignSelector::fromId((string) $this->argument('id')),
        (bool) $this->option('validate-only')
    )->onQueue('integrations');

    $this->info('Queued pause request');
    return self::SUCCESS;
}`,
      pros: [
        'Non‑blocking UX; retries/backoff handled by the queue.',
        'Easy to add de‑duplication/idempotency keys (selector + "PAUSED").',
        'Great for bulk operations.'
      ],
      cons: [
        'Not synchronous; no immediate provider response.',
        'Requires queue infrastructure + monitoring.'
      ],
    },
  ];

  return (
    <section className={styles.bg}>
      <div className="container">
        <div className="row">
          <div className="col col--12">
            <header style={{marginBottom: '1.25rem'}}>
              <Heading as="h2" className="margin-bottom--md">
                Use Case: Pause Campaign - it’s your choice
              </Heading>
              <p>
                Plenipotentiary gives you the tools - not a mandate. Pick the kickoff pattern that fits your
                codebase today, and refactor later without touching provider code. All four options below are
                valid; they simply trade off clarity, indirection, and operational concerns (e.g., queues).
              </p>
            </header>

            {approaches.map((a, idx) => (
              <article key={idx} style={{
                border: '1px solid var(--ifm-color-emphasis-200)',
                borderRadius: 12,
                padding: '1rem',
                marginBottom: '1.25rem',
                background: 'var(--ifm-background-surface-color)'
              }}>
                <Heading as="h3" className="margin-bottom--sm">{a.title}</Heading>

                <CodeBlock language="php">{a.code}</CodeBlock>

                <div style={{display: 'grid', gap: '0.75rem', gridTemplateColumns: '1fr 1fr'}}>
                  <div>
                    <Heading as="h4" className="margin-bottom--xs">Pros</Heading>
                    <ul style={{marginTop: 0}}>
                      {a.pros.map((p, i) => (
                        <li key={i}>{p}</li>
                      ))}
                    </ul>
                  </div>
                  <div>
                    <Heading as="h4" className="margin-bottom--xs">Cons</Heading>
                    <ul style={{marginTop: 0}}>
                      {a.cons.map((c, i) => (
                        <li key={i}>{c}</li>
                      ))}
                    </ul>
                  </div>
                </div>
              </article>
            ))}

            <footer style={{marginTop: '1.25rem'}}>
              <p style={{fontStyle: 'italic'}}>
                Recommendation: For reusable library code that must work across CLI/HTTP/jobs,
                the <strong>Action</strong> pattern hits a sweet spot. For quick scripts or tight scopes,
                the <strong>Explicit</strong> pattern is perfectly fine. For bulk or async workflows,
                wrap the Action in a queued <strong>Job</strong>.
              </p>
            </footer>
          </div>
        </div>
      </div>
    </section>
  );
}
