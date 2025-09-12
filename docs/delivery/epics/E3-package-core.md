# Epic E3: Package Core (MVP)

**Goal:** Establish the foundational package code for plenipotentiary-laravel.

## Scope

- Laravel ServiceProvider + config publishing
- Core contracts (AuthStrategy, TokenStore, Paginator, Transformer, ErrorMapper)
- Auth: TokenAuth
- Middleware: Retry, RateLimit, IdempotencyKey
- Exception taxonomy
- Orchestra Testbench setup

## Out of Scope

- Provider-specific code
- OAuth2 strategies
- Scaffolder (`pleni:make`)

## Deliverables

- [ ] ServiceProvider loads config, binds contracts
- [ ] TokenAuth strategy
- [ ] Retry + rate-limit middleware
- [ ] Exception classes (Transport/Auth/Domain/Validation)
- [ ] Contract test base
- [ ] CI pipeline green

## Risks

- Over-engineering contracts before usage
- Laravel version drift (10 vs 11)
