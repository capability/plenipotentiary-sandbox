# Plenipotentiary Sandbox

This repository is a **sandbox environment** for developing the [`plenipotentiary-laravel`](packages/plenipotentiary-laravel) package.

The sandbox provides:

- A full Laravel backend app (under `/apps/backend`)
- A frontend app (under `/apps/frontend`)
- Local dev tooling (Docker, devcontainers)
- A place to hand-craft provider/domain/resource adapters before scaffolding is automated

📖 See [docs/README.md](docs/README.md) for project documentation.

---

## What is `plenipotentiary-laravel`?

An **opinionated Laravel package** that makes it faster and safer to integrate with third-party APIs by:

- Generating provider/domain/resource scaffolding (DTOs, Repositories, Services)
- Handling auth, retries, pagination, idempotency, and error mapping out-of-the-box
- Using a **Generated vs User** split so developers can customise without fear of regeneration overwrites
- Providing contract tests and observability hooks

---

## Project Documentation

All planning, ADRs, epics, specs, and guides live under `/docs`.

Key entry points:

- [Project Charter & Technical Design](docs/charter/project-charter.md)
- [Architecture Decision Records (ADRs)](docs/adr/)
- [Delivery Plans & Epics](docs/delivery/)
- [Guides](docs/guides/)
- [OpenAPI Specs](docs/openapi/)

---

## Development Stack Notes

These guides relate to the **sandbox stack setup only**:

- [Cheatsheet](docs/stack-info/CHEATSHEET.md)
- [Devcontainers Guide](docs/stack-info/devcontainers-guide.md)
- [Environment Variables](docs/stack-info/ENV.md)
- [Onboarding](docs/stack-info/ONBOARDING.md)
- [Smoke Tests](docs/stack-info/SMOKE.md)
- [SSL Setup](docs/stack-i)
