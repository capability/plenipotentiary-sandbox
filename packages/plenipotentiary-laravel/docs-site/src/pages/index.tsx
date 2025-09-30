import type { ReactNode } from "react";
import clsx from "clsx";
import Link from "@docusaurus/Link";
import useDocusaurusContext from "@docusaurus/useDocusaurusContext";
import useBaseUrl from "@docusaurus/useBaseUrl";
import Layout from "@theme/Layout";
import HomepageFeatures from "@site/src/components/HomepageFeatures";
import HomepageTldr from "@site/src/components/HomepageTldr";
import MakeScaffoldSection from "@site/src/components/MakeScaffoldSection";
import DeveloperWorkflow from "@site/src/components/DeveloperWorkflow";
import ApiEndpointAdapterSection from "@site/src/components/ApiEndpointAdapterSection";
import HomepageFAQ from "@site/src/components/HomepageFAQ";
import HomepageBackground from "@site/src/components/HomepageBackground";
import SectionDivider from "@site/src/components/SectionDivider";
import Heading from "@theme/Heading";

import styles from "./index.module.css";

function HomepageHeader() {
  const { siteConfig } = useDocusaurusContext();
  const logoUrl = useBaseUrl("/img/logo-words-1024.png");

  return (
    <header className={clsx("hero hero--primary", styles.heroBanner)}>
      <div className="container">
        <img src={logoUrl} alt={siteConfig.title} width="300" />
        <Heading as="h1" className="visually-hidden">
          {siteConfig.title}
        </Heading>
        <p className="hero__subtitle">{siteConfig.tagline}</p>

        {/* State of the project banner */}
        <div
          style={{
            background: "var(--ifm-background-surface-color)",
            color: "var(--ifm-font-color-base)",
            border: "1px solid var(--ifm-color-emphasis-200)",
            borderRadius: "0.5rem",
            padding: "1rem",
            margin: "1rem auto",
            maxWidth: "640px",
            textAlign: "left",
          }}
        >
          <strong>Project Status:</strong>
          <p style={{ marginTop: "0.5rem", marginBottom: 0 }}>
            This project is in an <em>early sandbox phase</em>. Right now, the
            focus is on defining scaffolding files, folder structure, and
            generation commands.
            <br />
            It's not yet available as a Composer package. The examples you see
            here demonstrate the direction of travel and the developer
            experience we're aiming for.
          </p>
        </div>
        {/* End status banner */}

        <div className={styles.buttons} style={{ display: "none" }}>
          <Link
            className="button button--secondary button--lg"
            to="/docs/introduction"
          >
            Plenipotentiary Tutorial - 5min ⏱️
          </Link>
        </div>
      </div>
    </header>
  );
}

export default function Home(): ReactNode {
  const { siteConfig } = useDocusaurusContext();
  return (
    <Layout
      title={`${siteConfig.title} - Built for Laravel Integrators`}
      description="Description will go into a meta tag in <head />"
    >
      <HomepageHeader />
      <main>
        <HomepageFeatures />
        <HomepageTldr />
        <SectionDivider />
        <DeveloperWorkflow />
        <SectionDivider />
        <MakeScaffoldSection />
        <SectionDivider />
        <ApiEndpointAdapterSection />
        <SectionDivider />
        <HomepageFAQ />
        <SectionDivider />
        <HomepageBackground />
      </main>
    </Layout>
  );
}
