# Definitions

## Definition of Ready (DoR)

- Clear problem statement
- Contracts agreed (e.g., OpenAPI spec finalised)
- Dependencies identified
- Acceptance criteria written

## Definition of Done (DoD)

- Unit + contract tests green
- Docs updated (README + guides + changelog)
- No debug code or TODOs
- CI green
- Reviewed & merged via PR

## Coding Standards

- PHP 8.2+, strict types
- Pest for tests
- Static analysis (Larastan/PHPStan level 8)
- Code style (Laravel Pint)
- Autoload PSR-4; no globals
- Config/env handled via `config/pleni.php`
