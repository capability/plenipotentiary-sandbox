import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

export default function HomepageTldr() {
  return (
    <section className={styles.bg}>
  <div className="container">

    <div className="row">
      <div className="col col--12">

       
        
      </div>
    </div>
    <div className="row">
      <div className="col col--9"> 

        <Heading as="h2" className="margin-bottom--md"></Heading>
        <p>
          <strong>TL;DR:</strong> Think of it like <code>artisan:make</code> for third-party APIs: declare the provider, domain, context and resource and instantly scaffold the contracts, DTOs, gateways and test harness you need. You still implement the Adapter (its not magic), but the code now sits in a consistent, testable, tool-friendly structure. Flysystem-style consistency for APIs, while recognising not everything should be abstracted.
        </p>
        <p>
          Packages like Flysystem work because filesystems expose a timeless, minimal set of verbs (read, write, delete, list) that haven't changed in decades. That makes a thin abstraction viable. APIs are different, they evolve, deprecate and fragment. The right approach isn’t to pretend they can be abstracted as neatly, accept that churn is unavoidable but still provide guardrails and tools.
        </p>

      </div>

    
      

      <div className="col col--3">
        

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
