# Docs Site Development Guide

This Docusaurus site is configured with **Tailwind CSS v4** and **Infima** (Docusaurus default).

## Quick Start

From the **repository root**, run:

```bash
# Start the docs dev server (with hot reload)
just docs-dev

# Or simply bring it up in background
just docs-up
```

The site will be available at **http://localhost:3001**

## Common Workflows

### Making Changes to Components/Pages

1. Edit files in `src/components/` or `src/pages/`
2. Changes auto-reload in the browser (hot reload is enabled)
3. If using Tailwind classes, they're processed automatically

### Installing New Dependencies

```bash
# Install a new package
just docs-shell
npm install <package-name>
exit

# Then restart to pick up changes
just docs-restart
```

### After Installing Tailwind or Major Dependencies

If you've just installed Tailwind, PostCSS, or other build-critical dependencies:

```bash
# Rebuild the container to ensure all dependencies are baked in
just docs-rebuild
```

**Note:** The hot reload may be slower after installing Tailwind initially. Once the container is rebuilt, it should be fast again.

### Clearing Cache (when things act weird)

```bash
just docs-clear
just docs-restart
```

### Building for Production

```bash
just docs-build
```

### Viewing Logs

```bash
just docs-logs
```

## Styling with Tailwind v4 + Infima

This project uses both:
- **Infima** — Docusaurus's default CSS framework (good for content)
- **Tailwind CSS v4** — For utility-first styling

### Important Configuration

- **Preflight disabled**: `corePlugins: { preflight: false }` in `tailwind.config.js` to avoid conflicts
- **Dark mode**: Configured to use `[data-theme="dark"]` to match Docusaurus
- **Content paths**: Both `src/**` and `docs/**` are scanned for Tailwind classes

### Using Tailwind Classes

You can use Tailwind utilities anywhere in your React components:

```jsx
<div className="flex items-center gap-4 p-6 rounded-lg bg-blue-50">
  <Icon className="w-6 h-6 text-blue-600" />
  <p className="text-lg font-semibold">Hello Tailwind</p>
</div>
```

### Using Infima

Infima classes and CSS variables still work:

```jsx
<button className="button button--primary button--lg">
  Infima Button
</button>
```

## Troubleshooting

### "Sparkles is not defined" or Similar Errors

Make sure lucide-react is installed:

```bash
just docs-shell
npm install lucide-react
exit
just docs-restart
```

### Webpack Errors About Missing Modules

Usually fixed by rebuilding:

```bash
just docs-rebuild
```

### Changes Not Reflecting

1. Check logs: `just docs-logs`
2. Clear cache: `just docs-clear && just docs-restart`
3. Last resort: `just docs-rebuild`

### Slow Initial Build After Adding Tailwind

This is normal. Tailwind v4 processes all your content on first build. Subsequent builds are incremental and much faster.

## GitHub Actions

The deployment workflow (`.github/workflows/deploy-docusaurus.yml`) uses npm and should work fine with the current setup. It:

1. Uses `npm ci` for clean installs
2. Runs `npm run build`
3. Deploys to GitHub Pages

No changes needed for Tailwind — it's just another dependency that gets installed and processed during the build.

## Available Just Recipes

```bash
just docs-up              # Start docs container
just docs-down            # Stop docs container
just docs-dev             # Start with log streaming
just docs-restart         # Quick restart
just docs-rebuild         # Full rebuild (after dep changes)
just docs-logs            # Stream logs

just docs-install         # Install dependencies
just docs-install-clean   # Clean install (removes node_modules)
just docs-clear           # Clear Docusaurus cache
just docs-build           # Production build
just docs-typecheck       # Run TypeScript checks

just docs-shell           # Access container shell
just docs-smoke           # Quick health check
```

## Tips

- **Tailwind JIT**: Classes are generated on-demand, so bundle size stays small
- **Hot reload**: Works for both JS/JSX and CSS changes
- **TypeScript**: Enabled, but components can be `.js` or `.jsx` too
- **MDX**: Fully supported in `docs/` and `blog/`
