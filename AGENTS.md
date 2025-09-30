# Repository Guidelines

## Project Structure & Module Organization
- `apps/` hosts application shells; `apps/backend` wires providers like `GoogleAdsServiceProvider` for manual testing.
- `packages/` contains publishable libraries. The primary focus is `packages/plenipotentiary-laravel`, which delivers the Pleni Google Ads integration. See its local guide at `packages/plenipotentiary-laravel/AGENTS.md`.
- Root documentation (`docs/`, `pleni-inventory.md`, `DTO.md`) captures architecture references and DTO contracts; treat it as canonical when modelling new adapters.
- Shared contributor guidance for workspace-level automation lives in `packages/AGENTS.md`; follow both this file and the package-specific one when contributing.

## Build, Test, and Development Commands
- Install JS tooling (needed for Just recipes) via `pnpm install` at repo root.
- PHP dependencies for the Laravel package live under `packages/plenipotentiary-laravel`; bootstrap with `composer install` in that directory.
- Run the full test suite for the package with `./vendor/bin/pest` (package root). Targeted runs: `./vendor/bin/pest tests/Unit`, `./vendor/bin/pest tests/Feature`.
- Use `just` (root) for common automation shortcuts; run `just --list` to inspect available recipes.

## Coding Style & Naming Conventions
- PHP code follows PSR-12 with 4-space indentation and strict types. Contracts live under `src/Contracts`, implementations mirror domain namespaces (e.g., `Contexts/Search/Campaign`).
- Keep adapters provider-agnostic; provider-specific logic belongs under `Adapter` folders. DTOs reside in `DTO/` and use explicit accessor methods instead of public property mutation.
- Tests mirror production namespaces under `tests/`, using Pest naming (`*Test.php`) and descriptive `describe/it` blocks.

## Testing Guidelines
- Pest is the primary test runner. Mock external Google Ads services via the provided contracts; never hit real APIs during tests.
- Prefer unit tests beside the operation classes (`Adapter/*OperationTest.php` forthcoming) and feature tests covering IoC wiring.
- Maintain high coverage for selector validation, request mapping, and error translation; add regression tests whenever new provider context rules are introduced.

## Commit & Pull Request Guidelines
- Follow Conventional Commits semantics (`feat:`, `fix:`, `refactor:`) as seen in existing history.
- Squash noisy work-in-progress commits before opening a PR. Include context: linked Clubhouse/Jira issue, key behaviour notes, and any screenshots for UI-facing changes.
- PRs should list test commands executed (e.g., `./vendor/bin/pest`) and flag any intentionally failing suites.

For deeper agent responsibilities, consult `packages/AGENTS.md` and the package-level guidelines.
