import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

const items = [
  {
    q: 'Why Plenipotentiary? I can\'t even say it, never mind spell it.',
    a: <>Ambassador, Envoy, Emissary, Delegate, Proxy... all the good names are gone. Plenipotentiary captured what it does.
    Count yourself lucky, I had scattered terms like ForeignState and Ministry throughout an earlier
    iteration until I accepted that metaphors don't really belong in code. Either way, no ones 
    going to clash with a Pleni namespace?</>,
  },
  {
    q: 'Oh no... it\'s an API wrapper, isn\'t it?',
    a: <>Nope. It's an <em>orchestration + anti-corruption layer</em>. You still write Adapters; we add guardrails (retries, logging, idempotency, error mapping).</>,
  },
  {
    q: 'But this is just what you do when you integrate an API anyway, isn\'t it',
    a: <>Exactly! But its now repeatable, predictable, and testable instead of five ad-hoc services with five retry strategies.</>,
  },
  {
    q: 'I could achieve the same thing with a few files in my service layer!',
    a: <>You can. Then again for another service six months later… slightly differently because that API has its own quirks. By splitting Gateway and Adapter, I keep SDK churn isolated, make the integration testable with mocks, and guarantee things like idempotency and error mapping are always applied. Pleni's saves you from your future self.</>,
  },
  {
    q: 'What? So you want me to learn your approach AND a new API?',
    a: <>~10 minutes to learn Gateway ↔ Adapter. After that, you're just writing the same code you'd normally drop into a service class... but in the Adapter, where it's isolated. Pleni isn't an SDK wrapper! You still need to know the provider API. The difference is you only expose what you need, not the entire SDK surface. Over time the community can share common Adapters for basic ops, but the goal is clean contracts and boundaries, not hiding APIs behind another API.</>,
  },
  {
    q: 'Er… have you ever heard of Saloon / Guzzle / Laravel HTTP?',
    a: <>We love them. Use them in your Adapter if you like. Pleni isn't an HTTP client; it's the structure around your integration. It doesn't compete with Saloon, it gives Saloon a predictable home.</>,
  },
  {
    q: 'Er… have you ever heard of ETL / Pipes / Workflows?',
    a: <>Yes. They're great... when you need a full pipeline orchestrator. Pleni isn't that. It's for exposing just the slice of a big API you need, fast, with guardrails you'll want when things hit production. Use ETL for data pipelines; use Pleni for integrations inside your app.</>,
  },
  {
    q: 'But... it IS an API wrapper, isn\'t it?',
    a: <>Okay Yes... in the sense that we all wrap code around APIs/SDKs... but this wrapper adds clean contracts, testability, idempotency, error handling, queuing, extensibility, and the ability to swap out adapters... which I think is a good thing.</>,
  },
];

export default function HomepageFAQ() {
  return (
    <section className={styles.faqSection}>
      <div className="container">
        <div className="row">
          <div className="col col--12">
            <Heading as="h2" className="margin-bottom--lg">
              FAQ (a.k.a. things you were about to comment)
            </Heading>

            <div className="row">
              {items.map(({ q, a }, idx) => (
                <div key={idx} className="col col--12 margin-bottom--sm">
                  <h3 className={styles.faqQuestion}>{q}</h3>
                  <p>{a}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
