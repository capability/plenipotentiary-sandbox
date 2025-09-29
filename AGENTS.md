# Repository Guidelines

## Project Structure & Module Organization
- `apps/backend` – Laravel 11 sandbox powering API and queue work; Pest suites live in `apps/backend/tests`.
- `packages/plenipotentiary-laravel` – publishable library (`Plenipotentiary\\Laravel\\`); keep providers and adapters under `src/Pleni/*` with matching tests in `tests/`.
- `apps/frontend` – Nuxt 2 UI for workflow smoke tests; components in `src/components`, routes under `pages/`.
- `docs/` tracks ADRs, delivery plans, and onboarding; update when architecture or process shifts.
- `infra/` plus `docker-compose*.yml` manage local services; extend these files instead of crafting ad-hoc scripts.

## Build, Test, and Development Commands
- `just install` and `just fe-install` bootstrap Composer and pnpm deps inside containers.
- `just up` starts api/web/db/cache; `just quick-up-backend-ssl` adds HTTPS proxies; `just down` shuts services off.
- `just test` runs backend and package Pest suites; narrow focus with `vendor/bin/pest --colors=always` inside `apps/backend` or `packages/plenipotentiary-laravel`.
- Frontend work happens in the `frontend` service: `just fe-dev` tails Nuxt logs, `pnpm test:unit` runs Jest, and `pnpm run build` validates bundles.

## Coding Style & Naming Conventions
- PHP follows Laravel Pint (PSR-12). Use `just pint` to lint and `just pint-fix` before commits. Contracts live in `src/Contracts`; gateways/adapters belong under provider/context folders in `src/Pleni/`.
- Tests mirror namespaces with `<Subject>Test.php`; stash fixtures and fakes in `tests/Support`.
- Frontend code relies on ESLint (`@nuxtjs`) and Prettier (`pnpm lint`, `pnpm format`); keep Vue filenames kebab-case and prefer `<script setup lang="ts">`.

## Testing Guidelines
- Pest drives backend and package suites; reset state with Laravel refresh traits and pair new contracts with coverage in `tests/Contracts` and `tests/Integration`.
- Run `just ci-backend-sqlite` and `just ci-package` before PRs to mirror CI. Coverage thresholds live in `phpunit.xml`; verify adapters handle success and failure paths.
- Frontend specs sit beside components (`Component.spec.ts`); use `pnpm test:unit -- --watch` for rapid feedback.

## Commit & Pull Request Guidelines
- Branches follow `CONTRIBUTING.md`: `epic/e<n>-<slug>`, `feature/e<n>-<seq>-<slug>`, `fix|chore|docs/<slug>`.
- Commits use Conventional Commits (`feat(scope): summary`, `fix: namespace cleanup`); keep subjects ≤50 characters and add bodies for behavioural changes.
- PRs link the Task issue, confirm Definition of Done, and call out config/schema changes. Attach screenshots or logs for UI and external integrations.

## Agent Orchestration Notes
- The `Plenipotentiary` agent coordinates contract-driven flows: invoke Gateways and Repositories, persist inbound DTOs, and emit operation summaries.
- Keep DTOs provider-agnostic and avoid importing provider SDKs outside adapters. House provider credentials, quirks, and error mapping under `packages/plenipotentiary-laravel/src/Pleni/*`.
- Need package-specific agent responsibilities? See `packages/plenipotentiary-laravel/AGENTS.md`.
