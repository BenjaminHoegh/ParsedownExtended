import { themes as prismThemes } from 'prism-react-renderer';
import type { Config } from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';
import remarkMath from 'remark-math';
import rehypeKatex from 'rehype-katex';

const config: Config = {
  title: 'ParsedownExtended',
  favicon: 'img/favicon.ico',

  url: 'https://benjaminhoegh.github.io',
  baseUrl: '/ParsedownExtended/',

  // GitHub pages deployment config.
  organizationName: 'BenjaminHoegh',
  projectName: 'ParsedownExtended',

  onBrokenLinks: 'warn',
  onBrokenMarkdownLinks: 'warn',

  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'classic',
      {
        docs: {
          includeCurrentVersion: false,
          routeBasePath: '/',
          sidebarPath: './sidebars.ts',
          remarkPlugins: [remarkMath],
          rehypePlugins: [rehypeKatex],
          editUrl:
            'https://github.com/BenjaminHoegh/ParsedownExtended/tree/docs/',
        },
        theme: {
          customCss: './src/css/custom.css',
        },
      } satisfies Preset.Options,
    ],
  ],

  markdown: {
    mermaid: true,
  },
  themes: ['@docusaurus/theme-mermaid'],

  themeConfig: {
    mermaid: {
      theme: { light: 'neutral', dark: 'dark' },
    },
    image: 'https://github.com/BenjaminHoegh/ParsedownExtended/raw/gh-pages/img/parsedownExtended.png',
    navbar: {
      title: 'ParsedownExtended',
      logo: {
        alt: 'ParsedownExtended Logo',
        src: 'https://github.com/BenjaminHoegh/ParsedownExtended/raw/docs/parsedownExtended.png',
      },
      items: [
        {
          type: 'docsVersionDropdown',
          position: 'right',
          dropdownActiveClassDisabled: true,
        },
        {
          href: 'https://github.com/BenjaminHoegh/ParsedownExtended',
          label: 'GitHub',
          position: 'right',
        },
      ],
    },
    prism: {
      additionalLanguages: ['php', 'bash'],
      theme: prismThemes.github,
      darkTheme: prismThemes.vsDark,
    },
  } satisfies Preset.ThemeConfig,
};

export default config;
