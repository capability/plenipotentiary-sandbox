import {themes as prismThemes} from 'prism-react-renderer';
import type {Config} from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)
const config = {
  title: 'Plenipotentiary',
  tagline: 'A Laravel-first orchestration and anti-corruption layer for large APIs - not a wrapper!',
  url: 'http://localhost:3001',
  baseUrl: '/',
  favicon: 'img/favicon.ico',
  organizationName: 'capability',   // GitHub org/user
  projectName: 'plenipotentiary-sandbox', // repo name
  themes: ['@docusaurus/theme-live-codeblock'],
  presets: [
    ['@docusaurus/preset-classic',
      {
        docs: {
          path: 'docs',
          sidebarPath: require.resolve('./sidebars.ts'),
          editUrl: 'https://github.com/capability/plenipotentiary/',
          showLastUpdateAuthor: true,
          showLastUpdateTime: true,
        },
        blog: false,
        theme: { customCss: require.resolve('./src/css/custom.css') }
      }
    ]
  ],
  themeConfig: {
    colorMode: {
      defaultMode: 'dark',
      respectPrefersColorScheme: false,
    },
    prism: {
      theme: prismThemes.dracula,
      darkTheme: prismThemes.dracula,
    },
    navbar: {
      title: 'Plenipotentiary',
      logo: {
        alt: 'Plenipotentiary Logo',
        src: 'img/logox32.svg',
      },
      items: [
        { type: 'doc', docId: 'introduction', label: 'Docs', position: 'left' },
        { href: 'https://github.com/samdavey/plenipotentiary', label: 'GitHub', position: 'right' },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        { title: 'Docs', items: [{ label: 'Introduction', to: '/docs/introduction' }] },
        { title: 'Community', items: [{ label: 'Issues', href: 'https://github.com/samdavey/plenipotentiary/issues' }] }
      ],
      copyright: `© ${new Date().getFullYear()} Plenipotentiary`,
    }
  }
};
export default config;

