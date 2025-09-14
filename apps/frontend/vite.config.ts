/// <reference types="node" />

import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue'; // or react if that’s your stack

function parseSourceMap(value?: string): boolean | 'hidden' | 'inline' | false {
  if (!value) return false;
  const v = value.toLowerCase();
  if (v === 'true') return true;
  if (v === 'false') return false;
  if (v === 'hidden') return 'hidden';
  if (v === 'inline') return 'inline';
  return false;
}

export default defineConfig(({ mode }) => {
  const sm =
    mode === 'production'
      ? parseSourceMap(process.env.SOURCEMAPS)
      : true;

  const frontendHost =
    process.env.FRONTEND_HOST ||
    `ui.${process.env.PROJECT_SLUG || 'plenipotentiary-sandbox'}.test`;

  return {
    build: {
      sourcemap: sm,
    },
    plugins: [vue()],
    server: {
      host: '0.0.0.0',
      allowedHosts: [frontendHost, 'localhost'],
      hmr: {
        host: frontendHost,
        protocol: 'wss',
        clientPort: 443,
      },
    },
  };
});