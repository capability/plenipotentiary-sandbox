# Contributing

## Workflow
1. Open a **Task** issue using the “Task” template.
2. Create a branch:
   - Epics: `epic/e<n>-<slug>` (e.g., `epic/e1-internal-api`)
   - Features: `feature/e<n>-<seq>-<slug>` (e.g., `feature/e1-03-customers`)
   - Fixes: `fix/<slug>`  ·  Chores: `chore/<slug>`  ·  Docs: `docs/<slug>`
3. TDD: RED → GREEN → REFACTOR.
4. Open a PR; the PR template includes the **Definition of Done** checklist.
5. Link the Task and/or Epic in the PR.
6. CI must be green; at least one review for protected branches.

## Commit messages
Use Conventional Commits:
- `feat(e1-03): add customers endpoint`
- `test(e1-03): RED contract tests for pagination`
- `refactor(e1-03): extract cursor paginator`

Subject ≤50 chars when possible; add a body for context.

## Definition of Done
- [ ] Code + tests
- [ ] Docs updated
- [ ] CI green

## Branch protection (suggested)
Require passing checks: tests, static analysis, code style.
