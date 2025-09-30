# Iteration Spike

This version aims for robust contracts and testing by seperating each operation and having Request and Response Mappers. I think this is too hard of a sell 
to onboard developers who are already happy enough to shove all the operations into a single service layer. Parking if I need to come back to it.

# Plenipotentiary Sandbox

This repository is a **sandbox environment** for developing the [`plenipotentiary-laravel`](packages/plenipotentiary-laravel) package.

The sandbox provides:

- A full Laravel backend app (under `/apps/backend`)
- A frontend app (under `/apps/frontend`)
- Local dev tooling (Docker, devcontainers)
- A place to hand-craft provider/domain/resource adapters before scaffolding is automated

📖 See [docs/README.md](docs/README.md) for project documentation.

---

## Package Documentation

For detailed package documentation, see the [Docusaurus site](https://pleni.dev) which includes:

- **Introduction**: What Plenipotentiary is and what it trying to achieve (and not achieve)
- **TODO Concepts**: Contracts, DTOs, Gateways, Workflows
- **TODO Providers**: Google Ads, OpenAI, eBay implementations
- **TODO Getting Started**: Installation and quickstart guides
- **TODO Examples**: Real-world usage patterns

---

## What is `plenipotentiary-laravel`?

A **Laravel-first orchestration and anti-corruption layer** for large APIs - not a wrapper! It makes it faster and safer to integrate with third-party APIs by:

### **Core Architecture**

- **Gateway/Adapter Pattern**: Clean separation between your application and provider APIs
- **Result Pattern**: Consistent error handling with `Result::ok()`, `Result::err()`, `Result::invalid()`
- **Contract-Driven Development**: Provider-agnostic interfaces with concrete implementations

### **Two-Tier Adapter Approach**

1. **ApiCrudAdapterContract**: For traditional CRUD operations (create, find, update, delete)
2. **ApiEndpointAdapterContract**: For flexible, operation-based API calls that don't fit CRUD patterns

### **Built-in Capabilities**

- **Idempotency**: Automatic retry/race protection with configurable storage
- **Logging**: Structured logging with PSR-3 compatibility
- **Error Mapping**: Provider-specific error translation
- **Validation**: Pre-flight validation with detailed violation reporting
- **Authentication**: Multiple auth strategies (OAuth2, HMAC, Token-based)

### **Why This Approach Works**

- **Preserves Provider Semantics**: Each API keeps its natural operation names
- **Avoids Abstraction Leakage**: No forced translation between different vocabularies
- **Enables Gradual Migration**: Start with flexible endpoint adapters, migrate to CRUD when patterns emerge
- **Maintains Architecture Benefits**: Still follows Gateway/Adapter pattern with logging, idempotency, and error handling

---

## Current Provider Examples

- **Google Ads**: Campaign management with CRUD operations
- **OpenAI**: AI operations (completions, images, fine-tuning)
- **eBay Browse**: Marketplace operations (search, items, offers)

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
- [SSL Setup](docs/stack-info/SSL.md)

---

## Project Status

This project is in an **early sandbox phase**. Right now, the focus is on:

- Defining scaffolding files, folder structure, and generation commands
- Hand-crafting provider/domain/resource adapters
- Building out the core orchestration patterns
- Creating comprehensive documentation and examples

It's not yet available as a Composer package. The examples you see here demonstrate the direction of travel and the developer experience we're aiming for.

---

## Quick Example

```php
// CRUD operations for structured APIs
$result = $campaignGateway->create($campaignDto);

// Flexible operations for diverse APIs
$result = $openaiGateway->call('createCompletion', [
    'model' => 'gpt-3.5-turbo',
    'messages' => [['role' => 'user', 'content' => 'Hello!']]
]);

// Consistent error handling across all operations
if ($result->isOk()) {
    $data = $result->unwrap();
} elseif ($result->isInvalid()) {
    $violations = $result->violations();
} else {
    $error = $result->error();
}
```

## About

No description, website, or topics provided.

### Resources

Readme

### License

MIT license

### Code of conduct

Code of conduct

### Contributing

Contributing

### Security policy

Security policy
