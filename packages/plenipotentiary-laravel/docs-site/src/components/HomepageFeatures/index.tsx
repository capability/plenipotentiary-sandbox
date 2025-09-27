import type {ReactNode} from 'react';
import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

type FeatureItem = {
  title: string;
  png: string;
  description: ReactNode;
};

const FeatureList: FeatureItem[] = [
  {
    title: 'Your Domain, Predictable',
    png: '/img/orchestration-layer.png',
    description: (
      <>
        Stable contracts, no SDK noise: Gateways give you a clean entry point -  
        always return Result, always log, retry, and handle idempotency. Your app only ever sees predictable DTOs.
      </>
    ),
  },
  {
    title: 'Adapters, Your Way',
    png: '/img/anti-corruption-layer.png',
    description: (
      <>
        Expose just the surface you need: Write a small adapter around a single API 
        call (e.g. “push invoice to Xero”) or expand to cover a whole SDK. Either way, 
        guardrails and structure are already in place.
      </>
    ),
  },
  {
    title: 'Built For Integrators',
    png: '/img/the-integration-port.png',
    description: (
      <>
        Solve real API pain: Aimed at Laravel teams working with large APIs (Google Ads, 
        eBay, Xero). Not a “universal wrapper” - just the integration 
        contract layer you’d end up writing anyway.
      </>
    ),
  },
];

function Feature({title, png, description}: FeatureItem) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
	  	<img src={png} alt={title} width="200" />
      </div>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures(): ReactNode {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
