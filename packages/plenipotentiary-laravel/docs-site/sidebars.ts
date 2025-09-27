import type { SidebarsConfig } from '@docusaurus/plugin-content-docs';

const sidebars: SidebarsConfig = {
  docs: [
    'introduction',
    {
      type: 'category',
      label: 'Getting Started',
      collapsed: false,
      items: [
        'getting-started/installation',
        'getting-started/quickstart',
      ],
    },
    {
      type: 'category',
      label: 'Core Concepts',
      items: [
        'concepts/contracts',
        'concepts/dtos',
        'concepts/gateways',
        'concepts/workflows',
        'concepts/logging',
        'concepts/testing',
      ],
    },
    {
      type: 'category',
      label: 'Providers',
      items: [
        'providers/google/overview',
        'providers/ebay/overview',
        // add more as you scaffold them
      ],
    },
    'faq',
  ],
};

export default sidebars;

