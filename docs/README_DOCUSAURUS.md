# Docusaurus Quick Reference

## TL;DR

- **Location**: `packages/plenipotentiary-laravel/docs-site/`
- **Package Manager**: npm (not pnpm)
- **Status**: ✅ Working, builds successfully
- **lucide-react**: ✅ Installed and functional (v0.544.0)
- **Tailwind CSS v4**: ✅ Installed and configured
- **Local URL**: http://localhost:3001

## Common Commands (Just Recipes - Recommended)

```bash
# From repository root - use these for Docker-based workflow
just docs-dev             # Start dev server with hot reload (streams logs)
just docs-up              # Start docs container in background
just docs-down            # Stop docs container
just docs-restart         # Quick restart
just docs-rebuild         # Full rebuild (after adding dependencies)

just docs-install         # Install dependencies
just docs-install-clean   # Clean install (removes node_modules)
just docs-clear           # Clear Docusaurus cache
just docs-build           # Production build
just docs-typecheck       # Run TypeScript checks

just docs-shell           # Access container shell
just docs-logs            # Stream container logs
just docs-smoke           # Health check
```

## Alternative: Direct npm Commands

```bash
# From repository root
npm run docs:dev      # Start development server
npm run docs:build    # Build for production
npm run docs:serve    # Serve production build locally

# From docs-site directory
cd packages/plenipotentiary-laravel/docs-site
npm install           # Install/update dependencies
npm start             # Start dev server (alias for npm run dev)
npm run build         # Build site
npm run serve         # Serve built site
```

## Adding Dependencies

### Using Just (Recommended for Docker workflow)

```bash
# Shell into the container
just docs-shell

# Inside container:
npm install package-name
npm install -D dev-package-name
exit

# Restart to pick up changes
just docs-restart

# If you added major build dependencies (like Tailwind):
just docs-rebuild
```

### Using npm directly

```bash
cd packages/plenipotentiary-laravel/docs-site
npm install package-name
npm install -D dev-package-name
```

## When Things Break

### Build Errors

1. **Check lucide-react imports**: All lucide icons must be imported
   ```typescript
   import { IconName } from "lucide-react";
   ```

2. **Check markdown files**: Escape curly braces that aren't JSX
   ```markdown
   Use \{Provider\} instead of {Provider} in text
   ```

3. **Clean rebuild with Just**:
   ```bash
   just docs-clear
   just docs-rebuild
   ```

4. **Clean rebuild manually**:
   ```bash
   cd packages/plenipotentiary-laravel/docs-site
   rm -rf node_modules package-lock.json .docusaurus build
   npm install
   npm run build
   ```

### GitHub Actions Fails

- Workflow uses npm (correct)
- Check that build passes locally first
- Ensure package-lock.json is committed
- DON'T commit pnpm-lock.yaml in docs-site

## Project Structure

```
packages/plenipotentiary-laravel/docs-site/
├── docs/                    # Markdown documentation
│   ├── concepts/           # Concept guides
│   └── ...
├── src/
│   ├── components/         # React components
│   │   ├── ApiFirstWorkflow/      # ✓ Uses lucide-react
│   │   └── ...
│   ├── css/               # Styles
│   └── pages/             # Custom pages
├── static/                # Static assets
├── docusaurus.config.ts   # Main configuration
├── package.json           # Dependencies (uses npm)
└── package-lock.json      # Lock file (npm, not pnpm)
```

## Important Notes

1. **Never use pnpm** in the docs-site directory
2. **Always escape placeholders** like `{Provider}` in markdown: `\{Provider\}`
3. **Import all lucide icons** before using them
4. **GitHub Actions uses npm** - this is intentional and correct
5. The **theme-live-codeblock is disabled** - enable it if you need live code editing

## Full Documentation

- **Detailed dev guide**: `packages/plenipotentiary-laravel/docs-site/DEVELOPMENT.md`
- Setup details: `/PACKAGE_MANAGER_GUIDE.md`
- Resolution history: `/ISSUE_RESOLUTION_SUMMARY.md`
- Docusaurus docs: https://docusaurus.io/docs

## Troubleshooting Checklist

- [ ] Am I in the correct directory? (`docs-site/`)
- [ ] Am I using npm (not pnpm)?
- [ ] Did I import lucide icons I'm using?
- [ ] Did I escape curly braces in markdown?
- [ ] Did I clear cache? (`rm -rf .docusaurus`)
- [ ] Does it build locally? (`npm run build`)

## Contact

If something isn't working:
1. Check the error message carefully
2. Review `/ISSUE_RESOLUTION_SUMMARY.md`
3. Try a clean rebuild
4. Check if it's a new issue or a known one from this guide
