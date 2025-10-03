# Package Manager Guide

## Overview

This repository uses a **hybrid package manager setup** to accommodate different parts of the project:

- **Root & Frontend App**: pnpm (monorepo workspace)
- **Docusaurus Documentation Site**: npm (standalone, CI/CD compatibility)
- **Backend Laravel Apps**: Composer (PHP)

## Directory Structure & Package Managers

```
plenipotentiary-sandbox/
├── package.json                    # Root: pnpm workspace coordinator
├── apps/
│   ├── frontend/                   # pnpm (Nuxt.js app)
│   │   ├── package.json
│   │   ├── pnpm-lock.yaml
│   │   └── pnpm-workspace.yaml
│   └── backend/                    # Composer (Laravel app)
│       └── composer.json
└── packages/
    └── plenipotentiary-laravel/
        ├── composer.json           # Composer (Laravel package)
        └── docs-site/              # npm (Docusaurus)
            ├── package.json
            └── package-lock.json
```

## Why This Setup?

### pnpm for Frontend Apps
- **Workspace support**: Shared dependencies across apps
- **Disk efficiency**: Symlinked node_modules
- **Faster installs**: Content-addressable storage

### npm for Docusaurus
- **GitHub Pages compatibility**: CI/CD uses npm by default
- **Simplicity**: No workspace complexity for standalone docs
- **Reliability**: Official Docusaurus support prioritizes npm

### Composer for PHP/Laravel
- **Standard**: PHP ecosystem standard
- **Laravel requirement**: Framework dependency management

## Working with Each Part

### Root Level Commands
```bash
# These are convenience wrappers
npm run docs:install    # Install docs-site dependencies
npm run docs:dev        # Start docs dev server
npm run docs:build      # Build docs for production
npm run docs:serve      # Serve built docs locally
```

### Docusaurus Site (`packages/plenipotentiary-laravel/docs-site/`)
```bash
cd packages/plenipotentiary-laravel/docs-site

# Install dependencies
npm install

# Development
npm run dev              # or npm start
npm run build
npm run serve

# Add new dependencies
npm install lucide-react
npm install -D @types/node
```

**Important**: Always use `npm` in the docs-site directory, never `pnpm`.

### Frontend App (`apps/frontend/`)
```bash
cd apps/frontend

# Install dependencies
pnpm install

# Development
pnpm run dev

# Add dependencies
pnpm add some-package
pnpm add -D some-dev-package
```

### Laravel Package (`packages/plenipotentiary-laravel/`)
```bash
cd packages/plenipotentiary-laravel

# Install dependencies
composer install

# Run tests
./vendor/bin/pest

# Add dependencies
composer require vendor/package
composer require --dev vendor/dev-package
```

## Common Issues & Solutions

### Issue: "lucide-react not found" or similar module errors in Docusaurus

**Solution**:
```bash
cd packages/plenipotentiary-laravel/docs-site
rm -rf node_modules package-lock.json
npm install
```

### Issue: pnpm commands fail in docs-site

**Problem**: Using wrong package manager for docs-site.

**Solution**: Always use `npm` in the docs-site directory. The docs-site is intentionally kept separate from the pnpm workspace.

### Issue: GitHub Actions deployment fails

**Solution**: The GitHub workflow is configured for npm. Don't commit `pnpm-lock.yaml` in the docs-site directory.

### Issue: Dependency version conflicts

**Check**:
```bash
# In docs-site
npm list package-name

# In frontend app
cd apps/frontend && pnpm list package-name
```

## CI/CD Configuration

### GitHub Actions (`.github/workflows/deploy-docusaurus.yml`)

Uses **npm** exclusively:
```yaml
- name: Install dependencies
  run: npm install
  
- name: Build site
  run: npm run build
```

This is intentional and should not be changed to pnpm without extensive testing.

## Adding New Dependencies

### To Docusaurus (React components, docs features)
```bash
cd packages/plenipotentiary-laravel/docs-site
npm install package-name
```

### To Root (shared Just recipes, etc.)
```bash
# Root level (if needed)
pnpm add -w package-name
```

### To Frontend App
```bash
cd apps/frontend
pnpm add package-name
```

## Best Practices

1. **Never mix package managers** in the same directory
2. **Check lock files** before committing:
   - docs-site should only have `package-lock.json`
   - frontend should only have `pnpm-lock.yaml`
3. **Use the convenience scripts** from root when possible
4. **Clean install** when switching branches with dependency changes
5. **Verify builds** locally before pushing to CI/CD

## Verification Commands

Run these to verify your setup is correct:

```bash
# From root
echo "Root package manager:"
cat package.json | grep packageManager

# Docusaurus
echo "Docusaurus lock file:"
ls -la packages/plenipotentiary-laravel/docs-site/package-lock.json
echo "Docusaurus dependencies:"
cd packages/plenipotentiary-laravel/docs-site && npm list --depth=0

# Frontend
echo "Frontend lock file:"
ls -la apps/frontend/pnpm-lock.yaml
```

## Migration Notes (Historical)

**Previous State**: Root level had conflicting pnpm installation with `@docusaurus/theme-live-codeblock` as a dev dependency, causing confusion.

**Current State**: Cleaned up. The dependency is properly managed in the docs-site package.json where it belongs.

## Questions?

If you're unsure which package manager to use:
- **React/Docusaurus docs**: npm
- **Nuxt frontend**: pnpm
- **PHP/Laravel**: Composer
- **Just recipes**: pnpm (root level)

When in doubt, check for existing lock files in the directory.
