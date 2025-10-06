# Plenipotentiary Sandbox

> **Current Status:** This is a **sandbox/development environment** for the Plenipotentiary Laravel package. The package is in active development and not yet available on Packagist.

## Stack

This monorepo contains:

- **Package Development** (`/packages/plenipotentiary-laravel`) - The core Laravel package
- **Documentation Site** (`/packages/plenipotentiary-laravel/docs-site`) - Docusaurus interactive documentation
- **Laravel Test Apps** (`/apps/backend`) - Full Laravel application for testing integrations
- **Development Tooling** - Docker, devcontainers, Justfile commands

**Technologies:** Laravel 11, Saloon, Docusaurus, Docker, Tailwind CSS

---

<div align="center">
  <img src="pleni_logo.svg" alt="Plenipotentiary Logo" width="200"/>

  # Plenipotentiary

  **Structure, scaffolding, and sanity for API/SDK integrations in Laravel**

  [![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

  [📖 Interactive Docs](https://pleni.dev) | [🚀 Quick Start](#quick-start) | [🎯 Patterns](#patterns) | [❓ FAQs](#faqs)
</div>

---

## What is Plenipotentiary?

**Plenipotentiary is not an API wrapper.** It's architectural patterns and scaffolding for Laravel that helps you maintain consistency across 5-15 different API integrations over 3-5 years and multiple developers.

### The Problem

Your Laravel app integrates with payment gateways, CRMs, advertising platforms, and more. Each uses different approaches: official SDKs, REST APIs, SOAP, emerging protocols like MCP. Over time, these integrations become a maintenance nightmare—scattered patterns, inconsistent error handling, business logic tightly coupled to vendor implementations.

### The Solution

**The Gateway/Adapter pattern** provides a stable architectural boundary between your application and external services. Plenipotentiary gives you:

✅ **Patterns** - Five proven patterns (CRUD, Operation, Procedure, REST, MCP) that match different API shapes
✅ **Scaffolding** - Artisan commands that generate folder structure, DTOs, Gateways, Adapters, and tests
✅ **Contracts** - Consistent `Result<T>` interface across all integrations with `isOk()`, `isErr()`, `isInvalid()`, `unwrap()`
✅ **Stability** - Gateway layer stays stable when provider APIs change—only Adapters need updates
✅ **Cross-cutting Concerns** - Idempotency, logging, retries, rate limiting, error mapping applied consistently

### What You Still Write

**You implement the actual API integration logic.** Plenipotentiary provides structure, not magic:

- **Adapters** - Your code that calls Stripe, Google Ads, eBay, etc.
- **DTOs** - Your domain objects (scaffolded from INPUT_SPEC you define)
- **Business Logic** - Actions, Jobs, Commands that use Gateways

Plenipotentiary gives you the **architectural guardrails**, you bring the **API knowledge**.

---

## Quick Start

### Google Ads Campaign CRUD

```bash
php artisan pleni:make:crud \
  --provider=Google \
  --domain=Ads \
  --resource=Campaign \
  --with-actions \
  --with-tests
```

**Generated structure:**
```
Pleni/Google/Ads/Contexts/Default/Campaign/
├── DTO/CampaignCanonicalDTO.php
├── Gateway/CampaignCrudGateway.php
├── Adapter/
│   ├── CampaignCreate.php    ← You implement this
│   ├── CampaignRead.php      ← You implement this
│   ├── CampaignUpdate.php    ← You implement this
│   └── CampaignDelete.php    ← You implement this
├── Actions/
│   └── CreateCampaignAction.php
└── Tests/
    └── CampaignCrudTest.php
```

### Usage in Your App

```php
// Controller, Job, Command - all the same interface
public function store(Request $req, CampaignGateway $gateway)
{
    $dto = CampaignCanonicalDTO::fromArray($req->validated());
    $result = $gateway->create($dto);

    if ($result->isOk()) {
        $campaign = $result->unwrap();           // Your canonical DTO
        $rawResponse = $result->rawResponse();   // Provider response for debugging
        return response()->json($campaign);
    }

    // Provider rejected data (validation failed on their end)
    if ($result->isInvalid()) {
        return response()->json([
            'message' => 'Google Ads rejected the campaign',
            'violations' => $result->violations()
        ], 422);
    }

    // Provider error (network, auth, rate limit, etc.)
    return response()->json($result->error(), 500);
}
```

---

## Patterns

Plenipotentiary provides **five patterns** for different API shapes. Choose the one that matches your integration:

| Pattern | Use When | Example APIs | Structure |
|---------|----------|--------------|-----------|
| **CRUD** | Managing resources with full lifecycle | Google Ads Campaigns, Stripe Customers | Create/Read/Update/Delete operations |
| **Operation** | Non-CRUD use cases (search, calculate, verify) | eBay Search, OpenAI Completions | Single-purpose operation classes |
| **Procedure** | Quick prototypes, admin tools | Internal APIs, one-off scripts | RPC-style dynamic operations |
| **REST** | Clean RESTful APIs where Saloon shines | GitHub API, most REST services | Native Saloon Request pattern |
| **MCP** | AI agents calling YOUR tools | Filesystem, Database, Custom tools | Reverse direction: AI → Your system |

**Learn more:** [Pattern Documentation](https://pleni.dev/docs/patterns)

---

## Architecture

```
Your Application → Gateway → Adapter → External API
(You write)      (Provided) (You write) (Third-party)
```

### The Flow

1. **Your Application Domain** - Controllers, Jobs, Commands, Actions (you write this)
2. **Gateway Layer** - Stable contracts, validation, policies (Plenipotentiary provides)
3. **Adapter Layer** - API integration logic (you write this)
4. **External API** - Stripe, Google, OpenAI, etc. (third-party)

### Why This Works

**Gateway = Stability.** Your application calls the Gateway, never the vendor API directly. When provider APIs change (and they will), only the Adapter changes—your Gateway contract stays stable. This is the core architectural principle that prevents integration chaos.

**Learn more:** [Architecture Documentation](https://pleni.dev/docs/architecture)

---

## Developer Workflow

Plenipotentiary follows an **API-first approach**. You learn the real API before building any abstractions:

### The Six Steps

1. **Start with the Real API** - Copy the provider's SDK example, make a real call, see what comes back
2. **Define Your INPUT_SPEC** - Codify the minimum data your application needs (not everything the API offers)
3. **Test Until Green** - Build request/response mappers, write tests for success and failures
4. **Call Through Gateway** - Gateway reveals the exact DTO structure based on your INPUT_SPEC
5. **Scaffold to Your Spec** - Generate DTOs and Factories that match what you learned
6. **Robustness Comes Online** - Idempotency, logging, retries activate automatically

**This is the opposite of most API wrappers.** Instead of starting with abstract DTOs and hoping they fit, you learn the API first, then codify your understanding.

**Learn more:** [Developer Workflow Documentation](https://pleni.dev/docs/developer-workflow)

---

## Cross-Cutting Concerns

The Gateway layer provides a **single, consistent location** to apply production-grade features across all integrations:

- **Validation** - Your app's requirements enforced before the call (via INPUT_SPEC)
- **Idempotency** - Safe retries, no duplicate charges
- **Error Handling** - Provider-specific errors mapped to domain exceptions
- **Rate Limiting** - Laravel RateLimiter integration
- **Retries** - Automatic exponential backoff
- **Logging** - Structured logging for all API calls
- **Observability** - Metrics, events, audit trails
- **Budget Controls** - For AI agent tool access (MCP pattern)

Without the Gateway pattern, these concerns scatter across controllers, jobs, and service classes. Impossible to maintain consistently across 5-15 integrations.

---

## Real-World Examples

### Google Ads Campaign Sync (CRUD Pattern)
Pauses/resumes ad groups based on stock availability. Includes Actions, Commands, Queue Jobs, and full test suite.

```bash
php artisan pleni:make:crud \
  --provider=Google \
  --domain=Ads \
  --resource=Campaign \
  --with-actions \
  --with-commands \
  --with-jobs \
  --with-tests
```

**Cross-cutting concerns:**
- Rate Limiting (Global) - Google Ads API has strict 10K requests/day limit
- Logging (Global) - Compliance audit trail for all campaign changes
- Error Mapping (Provider) - Maps Google SDK exceptions to domain errors
- Idempotency (Context) - Prevent duplicate updates when jobs retry

### eBay Browse API (Operation Pattern)
Backend API driving a custom JS frontend. HTTP Controllers for RESTful endpoints.

```bash
php artisan pleni:make:operation \
  --provider=eBay \
  --domain=Browse \
  --resource=SearchItems \
  --with-controller \
  --with-tests
```

### AI Log Analyzer (MCP Pattern)
AI agent reads application logs, analyzes errors, suggests fixes. Budget tracking prevents runaway costs.

```bash
php artisan pleni:make:mcp-tool \
  --server=Filesystem \
  --tool=ReadLogFile \
  --with-actions \
  --with-policies \
  --with-mcp-server \
  --with-audit \
  --with-tests
```

**Cross-cutting concerns:**
- Agent Budget Policy (Pattern) - Limit max tokens per agent invocation
- Agent Rate Limit (Pattern) - Max 100 tool calls/minute, 1000/hour
- Audit Policy (Pattern) - Full trail of all filesystem access by AI agents

**See more:** [Scaffolding Examples](https://pleni.dev/docs/scaffolding)

---

## AI Code Agents

**AI agents thrive on patterns.** Once you have real-world adapter examples, AI can generate additional adapters that follow your established conventions. With scaffolded tests already in place, it becomes a matter of briefly reviewing AI-generated code rather than writing from scratch; the patterns and test harness provide the guardrails.

---

## FAQs

### Can't all this be done in Saloon?

Saloon is a best-in-class HTTP transport layer. Plenipotentiary uses Saloon underneath and adds:
- **Patterns** (CRUD, Operation, Procedure, REST, MCP)
- **Gateway/Adapter separation** (stable vs provider-specific)
- **Scaffolding** (Artisan commands)
- **Cross-cutting concerns** (idempotency hints, error mapping)

> Saloon is the HTTP transport. Plenipotentiary is the integration architecture layer. They work together.

### Isn't this just an API wrapper?

It's an **orchestration + anti-corruption layer**. You still write Adapters; we add guardrails (retries, logging, idempotency, error mapping). The goal: make Google Ads (SDK) look like Mailchimp (REST) in YOUR system.

### Who should use this?

✅ **Good fit:**
- Laravel apps with 5+ external API integrations
- Teams mixing SDKs (Google, Stripe) with REST APIs
- Projects expecting 3+ year lifespans
- Developers who value explicit contracts over magic

✗ **Probably overkill:**
- Single API integration (just use Saloon/SDK directly)
- MVPs and prototypes
- Anyone looking for "quick and easy" magic solutions

**More answers:** [FAQs Documentation](https://pleni.dev/docs/faqs)

---

## Documentation

### 📖 Interactive Documentation
**[https://pleni.dev](https://pleni.dev)** - Full interactive documentation with live examples

### 📚 Key Documentation Pages

- **[Introduction](https://pleni.dev/docs/introduction)** - What Plenipotentiary is and isn't, the integration challenge
- **[Architecture](https://pleni.dev/docs/architecture)** - Gateway/Adapter pattern, four patterns explained
- **[Developer Workflow](https://pleni.dev/docs/developer-workflow)** - Six-step API-first approach
- **[Patterns](https://pleni.dev/docs/patterns)** - CRUD, Operation, Procedure, REST, MCP patterns in detail
- **[Scaffolding](https://pleni.dev/docs/scaffolding)** - Artisan commands, real-world examples
- **[FAQs](https://pleni.dev/docs/faqs)** - Common questions answered
- **[Why & Roadmap](https://pleni.dev/docs/why-roadmap)** - The story behind Plenipotentiary and future plans

---

## Why Plenipotentiary Exists

I've spent my career making one system talk to another. When Google sunset the AdWords API on April 27, 2022 and moved to the Google Ads API, one of my deepest integrations, built 10 years earlier, effectively became a new project just to get back to where I was before.

That experience reshaped how I build: **better boundaries, cleaner contracts, and more attention to SDK churn.**

This is not the only way to build integrations. It's not even the best way. It's just my way. If you like it, great! If you already have a strong approach, fantastic. If you think it's a bad idea, fair enough.

For me, I just wanted a tool that spins up a safe, predictable way to use a small slice of a big API or SDK without reinventing the guardrails every time.

**Read more:** [Why & Roadmap](https://pleni.dev/docs/why-roadmap)

---

## Roadmap

### Current Focus (Sandbox Phase)
- ✅ Core Gateway/Adapter architecture
- ✅ Five pattern implementations (CRUD, Operation, Procedure, REST, MCP)
- ✅ Interactive documentation site
- ✅ Real-world examples (Google Ads, eBay, OpenAI)
- 🚧 Scaffolding commands (`pleni:make:*`)
- 🚧 Community adapter examples
- 🚧 Package publication to Packagist

### Future Possibilities
- OpenAPI to DTO Generator
- Laravel Workflow integration
- gRPC transport support
- GraphQL Gateway pattern
- Event-driven adapters
- AI-powered integration builder

---

## Project Structure

```
plenipotentiary-sandbox/
├── apps/
│   └── backend/              # Laravel test application
├── packages/
│   └── plenipotentiary-laravel/
│       ├── src/              # Package source code
│       └── docs-site/        # Docusaurus documentation
├── docs/                     # Project planning, ADRs, guides
├── pleni_logo.svg           # Package logo
└── README.md                # This file
```

### Development Documentation

Sandbox-specific development guides:
- [Cheatsheet](docs/stack-info/CHEATSHEET.md)
- [Devcontainers Guide](docs/stack-info/devcontainers-guide.md)
- [Environment Variables](docs/stack-info/ENV.md)
- [Onboarding](docs/stack-info/ONBOARDING.md)

---

## Contributing

This project is in early sandbox phase. Suggestions, considerations, critiques, and potential problems are welcome. PRs encouraged.

---

## License

MIT License - see [LICENSE](LICENSE) file for details

---

## About the Name

**plenipotentiary** /ˌplɛnɪpəˈtɛn(t)ʃ(ə)ri/

A person `GATEWAY/ADAPTER`, invested with the full power of independent action on behalf of their government `DOMAIN`, typically in a foreign country `API_PROVIDER`.

Ambassador, Envoy, Emissary, Delegate, Proxy... all the good names were taken. Also, who's going to clash with a `Pleni` namespace?

---

<div align="center">

**[📖 Read the Full Documentation](https://pleni.dev)** | **[🚀 View Interactive Examples](https://pleni.dev)**

Built with battle scars, not theory. 🛡️

</div>
