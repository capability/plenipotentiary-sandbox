# Epic E2: AcmeCart / Backoffice Adapter (MVP)

**Goal:** Hand-build the first provider/domain/resource adapter for Customers, Orders, and OrderItems.

## Scope

- OpenAPI spec (`/docs/openapi/providers/acmecart/backoffice/openapi.yaml`)
- DTOs (Inbound/Outbound)
- Transformers
- Repositories
- Services
- Contract tests using HTTP fakes

## Out of Scope

- Scaffolder automation
- UI/controllers
- Queues/schedulers

## Deliverables

- [ ] OpenAPI spec committed & validated
- [ ] Customer DTO/Transformer/Repository + tests
- [ ] Order DTO/Transformer/Repository + tests
- [ ] OrderItem DTO/Transformer/Repository + tests
- [ ] Contract tests for errors + pagination

## Risks

- Spec drift between OpenAPI and code
- Time-boxing of initial implementation
