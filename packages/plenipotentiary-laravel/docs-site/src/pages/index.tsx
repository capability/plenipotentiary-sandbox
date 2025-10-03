import type { ReactNode } from "react";
import useDocusaurusContext from "@docusaurus/useDocusaurusContext";
import Layout from "@theme/Layout";
import Introduction from "@site/src/components/Introduction";
import TabbedContent from "@site/src/components/TabbedContent";

export default function Home(): ReactNode {
  const { siteConfig } = useDocusaurusContext();
  return (
    <Layout
      title={`${siteConfig.title} - Built for Laravel Integrators`}
      description="Description will go into a meta tag in <head />"
    >
      <main>
        {/* Project Status Banner */}
        <div className="bg-red-600 text-white py-6 px-4">
          <div className="max-w-7xl mx-auto">
            <h3 className="text-xl font-bold mb-3">Project Status:</h3>
            <p className="text-base leading-relaxed">
              This project is in an early sandbox phase. Right now, the focus is on defining scaffolding files, folder structure, and generation commands.
              It's not yet available as a Composer package. The examples you see here demonstrate the direction of travel and the developer experience we're aiming for.
            </p>
          </div>
        </div>

        <Introduction />
        <TabbedContent />
      </main>
    </Layout>
  );
}
