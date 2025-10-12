import type { SidebarsConfig } from "@docusaurus/plugin-content-docs";

const sidebars: SidebarsConfig = {
  docs: [
    "introduction",
    {
      type: "category",
      label: "Core Concepts",
      link: {
        type: "generated-index",
        description: "Core architectural concepts, patterns, and workflows that make up Plenipotentiary.",
      },
      items: [
        "core-concepts/architecture",
        "core-concepts/developer-workflow",
        "core-concepts/patterns",
        "core-concepts/scaffolding",
        "core-concepts/testing",
      ],
    },
    "faqs",
    "why-roadmap",
  ],
};

export default sidebars;
