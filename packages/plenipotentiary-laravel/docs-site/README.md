# Docusaurus Documentation Site

This website is built using [Docusaurus](https://docusaurus.io/), a modern static website generator.

## Package Manager

**This site uses npm** (not pnpm or yarn). While the monorepo root uses pnpm for the main applications, the documentation site is isolated and uses npm for consistency with GitHub Actions deployment.

## Local Development

### Using Docker (Recommended)

From the repository root:

```bash
docker-compose up docs
```

The site will be available at http://127.0.0.1:3001/

### Native Development

If running locally without Docker:

```bash
cd packages/plenipotentiary-laravel/docs-site
npm install
npm run dev
```

The site will be available at http://localhost:3000/

## Build

```bash
npm run build
```

This generates static content into the `build` directory.

## Deployment

The site automatically deploys to GitHub Pages when changes are pushed to the `main` branch. The deployment workflow is defined in `.github/workflows/deploy-docusaurus.yml`.

### Manual Deployment Trigger

You can manually trigger a deployment from the GitHub Actions tab using the "workflow_dispatch" option.

## Adding Dependencies

Always use npm to maintain consistency:

```bash
npm install <package-name>
```

After adding dependencies, rebuild the Docker container:

```bash
docker-compose build --no-cache docs
docker-compose up -d docs
```
