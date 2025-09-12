# Epic E0: Testing & Tooling (Foundational)

**Goal:** Establish the test and tooling foundations before building any package or adapter code.

## Scope

- Pest + Orchestra Testbench baseline
- Static analysis (Larastan/PHPStan)
- Code style enforcement (Laravel Pint)
- CI workflow (GitHub Actions)
- HTTP fakes and fixtures (JSON from /docs/openapi/examples)
- Contract test bases for CRUD, pagination, and error mapping

## Out of Scope

- Provider/domain/resource code
- Scaffolder automation
- Performance/load testing

## Deliverables

- [ ] Pest configured and running
- [ ] Static analysis set up
- [ ] Code style checks wired
- [ ] CI pipeline green (tests + analysis + style)
- [ ] Contract test base class in `tests/Contracts`
- [ ] Example fixtures committed and usable in tests

## Risks

- Delaying this work undermines TDD discipline
- Test suite could get slow if HTTP fakes not isolated

## Notes

This epic **must be complete (or nearly complete)** before starting E1 (Package Core) or E2 (First Adapter).
