# Project Documentation

This directory contains all project documentation for **plenipotentiary-laravel** and its sandbox.

---

## 📖 Key Documents

- **Charter & Technical Design**

  - [Project Charter](charter/project-charter.md)

- **Architecture Decisions (ADRs)**

  - [ADR Index](adr/)

- **Delivery & Planning**

  - [Roadmap](delivery/roadmap.md)
  - [Milestones](delivery/milestones.md)
  - [Work Breakdown Structure (WBS)](delivery/wbs.md)
  - [Definitions (DoR/DoD, coding standards)](delivery/definitions.md)
  - [Planning Process](delivery/planning.md)
  - [Epics](delivery/epics/)

- **Guides**

  - [Getting Started](guides/getting-started.md)
  - [Testing](guides/testing.md)
  - [Coding Standards](guides/coding-standards.md)
  - [OpenAPI Usage](guides/openapi.md)

- **OpenAPI Specs**
  - [AcmeCart / Backoffice](openapi/providers/acmecart/backoffice/openapi.yaml)

---

## 🏗️ Current Architecture

**Plenipotentiary** is a Laravel-first orchestration and anti-corruption layer for large APIs. It provides:

### **Core Patterns**

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

### **Provider Examples**

- **Google Ads**: Campaign management with CRUD operations
- **OpenAI**: AI operations (completions, images, fine-tuning)
- **eBay Browse**: Marketplace operations (search, items, offers)

---

## 📂 Directory Overview

docs/
├── charter/ # Project charter & technical design
├── adr/ # Architecture Decision Records
├── delivery/ # Roadmap, milestones, WBS, epics
├── guides/ # How-to guides for developers
├── openapi/
