# Work Breakdown Structure

## E1. Package Core (MVP)

- ServiceProvider + config
- Contracts (AuthStrategy, TokenStore, Paginator, ErrorMapper, Transformer)
- Auth: TokenAuth
- Middleware: Retry, RateLimit, Idempotency
- Exceptions taxonomy
- Testbench setup

## E2. AcmeCart / Backoffice Adapter (MVP)

- OpenAPI spec
- DTOs: Customers, Orders, OrderItems
- Transformers
- Repositories
- Contract tests with HTTP fakes

## E3. Testing & Tooling

- Contract test base classes
- Fake HTTP server / fakes
- CI pipeline

## E4. Observability (Baseline)

- Structured logging
- Correlation IDs
- Optional OTel spans

## E5. Scaffolder & Stubs

- `pleni:make` command
- Stub templates
- Overwrite policy (Generated vs User)

## E6. Docs & Examples

- Guides: Getting Started, Testing, OpenAPI usage
- Example app
