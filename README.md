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

  ---

  ### 🎯 New here? [**Visit the Interactive Documentation**](https://pleni.dev)

  The interactive docs are the **fastest way to understand** the scope and ambition of Plenipotentiary. Explore live examples, pattern comparisons, and scaffolding visualizations—all in a modern, navigable interface.

  **This README is a summary. The interactive docs tell the full story.**

</div>

---

## What is Plenipotentiary?

**Plenipotentiary is not an API wrapper.** It's architectural patterns and scaffolding for Laravel that helps you maintain consistency across heterogeneous integrations (SDKs, REST, SOAP) in your Laravel apps.

### The Problem

Your Laravel app integrates with **3-5+ different types of services**: Google Ads (official SDK), Mailchimp (REST), Stripe (official SDK), legacy SOAP, internal APIs. Each implemented differently.

### The Solution

**The Gateway/Adapter pattern** provides a stable architectural boundary between your application and external services. Plenipotentiary gives you:

- ✅ **Consistency** - Uniform interface across SDK, REST, SOAP, MCP integrations
- ✅ **Predictability** - Same patterns and contracts for ANY external service
- ✅ **Testability** - Same mocking strategy for all integrations
- ✅ **Discoverability** - New developers see Gateway pattern everywhere
- ✅ **Swappability** - Change providers without touching business logic
- ✅ **Focusability** - AI code agents perform best within defined, repeatable patterns. Repetition turns AI output into reliability.

**What you get:**
- **Patterns** - Five proven patterns (CRUD, Operation, Procedure, REST, MCP Proxy) that match different API shapes
- **Scaffolding** - Artisan commands that generate folder structure, DTOs, Gateways, Adapters, and tests
- **Contracts** - Consistent `Result<T>` interface across all integrations with `isOk()`, `isErr()`, `isInvalid()`, `unwrap()`
- **Stability** - Gateway layer stays stable when provider APIs change—only Adapters need updates
- **Cross-cutting Concerns** - Idempotency, logging, retries, rate limiting, error mapping applied consistently

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
│   ├── CampaignCrudAdapter.php  
│   ├── CampaignCreate.php    ← You implement this
│   ├── CampaignRead.php      ← You implement this
│   ├── CampaignReadMany.php  ← You implement this
│   ├── CampaignUpdate.php    ← You implement this
│   └── CampaignDelete.php    ← You implement this
├── Actions/
│   └── CreateCampaignAction.php
└── Tests/
    ├── Unit/
    │   ├── Adapter/
    │   │   ├── CampaignCreateTest.php
    │   │   ├── CampaignReadTest.php
    │   │   ├── CampaignReadManyTest.php
    │   │   ├── CampaignUpdateTest.php
    │   │   └── CampaignDeleteTest.php
    │   ├── DTO/
    │   │   └── CampaignCanonicalDTOTest.php
    │   └── Support/
    │       ├── CampaignIdempotencyHintsTest.php
    │       └── GoogleAdsErrorMapperTest.php
    ├── Integration/
    │   ├── CampaignCreateIntegrationTest.php
    │   ├── CampaignReadIntegrationTest.php
    │   ├── CampaignReadManyIntegrationTest.php
    │   ├── CampaignUpdateIntegrationTest.php
    │   └── CampaignDeleteIntegrationTest.php
    └── Feature/
        └── CampaignGatewayFeatureTest.php
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

Plenipotentiary provides **five patterns** for different API shapes. These patterns help you handle **heterogeneous integrations** (SDKs, REST, SOAP) with a consistent interface:

| Pattern       | Use When                                                                             | Example APIs                                                     | Return Type                                                           |
|---------------|--------------------------------------------------------------------------------------|------------------------------------------------------------------|-----------------------------------------------------------------------|
| **CRUD**      | Managing resources with full lifecycle                                               | Google Ads Campaigns, Stripe Customers                           | `Result<{Resource}CanonicalDTO>`                                      |
| **Operation** | Operations beyond CRUD that don't act on resource fields (search, calculate, verify) | eBay Search, OpenAI Completions                                  | `Result<{UseCase}DTO>`                                                |
| **Procedure** | Quick prototypes, admin tools                                                        | Internal APIs, one-off scripts                                   | `Result<mixed>`                                                       |
| **REST**      | Clean RESTful APIs using Saloon                                                      | CRUD-like: Todo API, Operation-like: OpenAI, Simple: Weather API | `Result<CanonicalDTO>` OR `Result<{UseCase}DTO>` OR `Saloon Response` |
| **MCP Proxy** | Controlled AI tool access (niche)                                                    | Proxy Database/Filesystem MCP servers                            | `Result<McpToolResult>`                                               |

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

Without the Gateway pattern, these concerns scatter across controllers, jobs, and service classes. Impossible to maintain consistently across heterogeneous integrations (SDKs, REST, SOAP).

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

### AI Log Analyzer Tool Proxy (MCP Proxy Pattern - Niche)
Proxy the Filesystem MCP server through your Laravel API. Claude/GPT calls YOUR endpoint, which forwards to the real Filesystem MCP server with budget tracking, rate limits, and audit logs.

```bash
php artisan pleni:make:mcp-proxy \
  --server=FilesystemMCP \
  --with-actions \
  --with-policies \
  --with-mcp-proxy \
  --with-audit \
  --with-tests
```

**Cross-cutting concerns:**
- MCP Proxy Budget Policy (Pattern) - Track and limit total MCP tool calls per day/hour
- MCP Proxy Rate Limit (Pattern) - Max 100 proxied tool calls/minute, 1000/hour
- MCP Proxy Audit Policy (Pattern) - Full audit trail of all MCP tool calls proxied through Laravel

**See more:** [Scaffolding Examples](https://pleni.dev/docs/scaffolding)

---

## AI Code Agents

**AI agents thrive on patterns.** Once you have real-world adapter examples, AI can generate additional adapters that follow your established conventions. With scaffolded tests already in place, it becomes a matter of briefly reviewing AI-generated code rather than writing from scratch; the patterns and test harness provide the guardrails.

---

## FAQs

### Can't all this be done in Saloon?

Saloon is a best-in-class HTTP transport layer. Plenipotentiary uses Saloon underneath and adds:
- **Patterns** (CRUD, Operation, Procedure, REST, MCP Proxy)
- **Gateway/Adapter separation** (stable vs provider-specific)
- **Scaffolding** (Artisan commands)
- **Cross-cutting concerns** (idempotency hints, error mapping)

> Saloon is the HTTP transport. Plenipotentiary is the integration architecture layer. They work together.

### Isn't this just an API wrapper?

It's an **orchestration + anti-corruption layer**. You still write Adapters; we add guardrails (retries, logging, idempotency, error mapping). The goal: make Google Ads (SDK) look like Mailchimp (REST) in YOUR system.

### Who should use this?

**Key Insight:** It's about integration diversity, not team size. A solo developer managing 8 different vendor integrations (Google Ads SDK, Mailchimp REST, Stripe SDK, legacy SOAP) benefits more than a 10-person team with 2 similar REST APIs.

✅ **Good fit:**
- Apps with 3-5+ heterogeneous integrations (mixing SDKs, REST, SOAP)
- Solo developers managing 5+ different vendor integrations
- Projects expecting 3+ year lifespans
- Agencies building consistent integration patterns
- Developers who value explicit contracts over magic

✗ **Probably overkill:**
- 1-2 integrations using similar APIs (just use Saloon/SDK directly)
- MVPs and prototypes (premature architecture)
- Small projects with homogeneous integrations (all REST or all SDK)
- Teams allergic to structure and patterns
- Anyone looking for "quick and easy" magic solutions

**More answers:** [FAQs Documentation](https://pleni.dev/docs/faqs)

---

## Documentation

### 📖 Interactive Documentation
**[https://pleni.dev](https://pleni.dev)** - Full interactive documentation with live examples

### 📚 Key Documentation Pages

- **[Introduction](https://pleni.dev/docs/introduction)** - What Plenipotentiary is and isn't, the integration challenge
- **[Architecture](https://pleni.dev/docs/architecture)** - Gateway/Adapter pattern, five patterns explained
- **[Developer Workflow](https://pleni.dev/docs/developer-workflow)** - Six-step API-first approach
- **[Patterns](https://pleni.dev/docs/patterns)** - CRUD, Operation, Procedure, REST, MCP Proxy patterns in detail
- **[Scaffolding](https://pleni.dev/docs/scaffolding)** - Artisan commands, real-world examples
- **[FAQs](https://pleni.dev/docs/faqs)** - Common questions answered
- **[Why & Roadmap](https://pleni.dev/docs/why-roadmap)** - The story behind Plenipotentiary and future plans

---

## Why Plenipotentiary Exists

I've spent my career making one system talk to another. When Google sunset the AdWords API on April 27, 2022 and moved to the Google Ads API, one of my deepest integrations, built 10 years earlier, effectively became a new project just to get back to where I was before.

That experience reshaped how I build: **better boundaries, cleaner contracts, and isolation from vendor changes.**

But the real problem isn't vendor churn—it's chaos. In any Laravel app with multiple integrations, you end up with Google Ads (official SDK), Mailchimp (REST), Stripe (official SDK), legacy SOAP services, and internal APIs—each implemented differently by different developers. Without a pattern, every integration is a special snowflake: different error handling, different return types, different testing strategies, different logging approaches.

This is not the only way to build integrations. It's not even the best way. It's just my way—an opinionated approach to make heterogeneous integrations look uniform in YOUR system. Gateway pattern provides the stable interface your app depends on—whether the vendor uses an SDK, REST, SOAP, or something else entirely.

**Read more:** [Why & Roadmap](https://pleni.dev/docs/why-roadmap)

---

## Roadmap

### Current Focus (Sandbox Phase)
- ✅ Core Gateway/Adapter architecture
- ✅ Five pattern implementations (CRUD, Operation, Procedure, REST, MCP Proxy)
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
- AI-powered integration builder (MCP Proxy pattern + Gateway contracts)

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
