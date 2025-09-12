# Epic E5: Scaffolder & Stubs

**Goal:** Build `pleni:make` command and safe scaffolding system.

## Scope

- Artisan command `pleni:make`
- Stub templates for DTOs, Transformers, Repositories, Services
- Generated vs User split (abstract base vs final class)
- Overwrite policy (only `Generated/*` touched with `--force-generated`)
- Checksums + headers in generated files

## Out of Scope

- OpenAPI-driven codegen (future)
- Multi-framework adapters

## Deliverables

- [ ] `pleni:make` artisan command
- [ ] Stubs in `stubs/`
- [ ] Overwrite policy tested
- [ ] CLI options (`--auth`, `--client`, `--base-url`, `--with-tests`, `--force-generated`)

## Risks

- Developer confusion about Generated vs User split
- Stub drift vs real provider patterns
