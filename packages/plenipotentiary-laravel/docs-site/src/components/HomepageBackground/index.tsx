import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

export default function HomepageBackground() {
  return (
    <section className={styles.bg}>
  <div className="container">
    <div className="row">
      <div className="col col--12">
        <Heading as="h2" className="margin-bottom--md">Why this exists</Heading>
        <p>
          I’ve spent my whole career making one system talk to another. Over those years PHP has improved,
          frameworks have matured, and our expectations of testing and code robustness have gone up. My earliest
          attempts, long before Laravel had a foothold, were brittle. I’ve thrown together integrations quickly; they
          worked, but they could have been better.
        </p>
        <p>
          I’ve also taken some hard knocks. When Google sunset the AdWords API on April 27, 2022 and moved to the
          Google Ads API, one of my deepest integrations, built 10 years earlier, effectively became a new project
          just to get back to where I was before. That experience reshaped how I build: better boundaries, cleaner
          contracts, and more attention to SDK churn.
        </p>
        <p>
          There are many ways to skin a cat, and I’ve tried most of them. What you see here is an opinionated way
          to keep your domain clean and testable while still relying on third-party code that will change. SDK churn
          is real. That quick script that gets you moving today can just as easily stall you in a few years’ time.
        </p>
        <p>
          This is not the only way to build integrations. It’s not even the best way. It’s just my way. If you like it, great!
          If you keep building integrations a new way every time an API feels different, this opinionated structure will help. 
          If you already have a strong approach, fantastic, share your experience and stick with it. And if you think it’s a bad idea, fair enough.
        </p>
        <p>
          For me, I just wanted a tool that spins up a safe, predictable way to use a small slice of a big API or SDK without
          reinventing the guardrails every time. This is what I’ve come up with. And I thought I’d share it.
        </p>
        <p>
          It’s one opinionated approach among many. Suggestions, considerations, critiques, and potential problems are welcome.
          PRs encouraged.
        </p>
      </div>
    </div>
  </div>
</section>
  );
}
