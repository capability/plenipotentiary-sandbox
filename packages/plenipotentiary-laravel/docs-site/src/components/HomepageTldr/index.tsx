import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

export default function HomepageTldr() {
  return (
    <section className={styles.bg}>
  <div className="container">
    <div className="row">
      <div className="col col--5">

      <Heading as="h2" className="margin-bottom--md">TL;DR:</Heading>
        <p>
          Think of it like <code>artisan:make</code> for third-party APIs: you declare the
          provider, service, context and resource (e.g. Google / Ads / Search|PMax|Demand / Campaign),
          and it scaffolds the contracts, DTOs, adapters, and gateways you need, so you can
          focus on the API quirks instead of rewriting boilerplate.
        </p>
        
      </div>

      <div className="col col--1">  </div>

      <div className="col col--6">
        


        <div className="card" style={{ maxWidth: 520 }}>
          <div className="card__header">
            <h3 className="margin-bottom--xs">plenipotentiary</h3>
            <div className="text--secondary">/ˌplɛnɪpəˈtɛn(t)ʃ(ə)ri/</div>
          </div>
          <div className="card__body">
            <p className="margin--none">
              a person, especially a diplomat, invested with the full power of
              independent action on behalf of their government, typically in a
              foreign country.
            </p>
          </div>
        </div>


      </div>
    </div>
  </div>
</section>
  );
}
