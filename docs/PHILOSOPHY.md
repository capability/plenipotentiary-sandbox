# Plenipotentiary Philosophy

Plenipotentiary exists to provide a **predictable, opinionated pattern for exposing external APIs in Laravel applications**.  
It is a **roll‑your‑own approach**: you don’t get a massive pre‑built SDK wrapper, but instead a small structured toolkit to build **just the edge surface you care about**.

---

## 🌱 Core Idea
Most APIs (Google Ads, Xero, Salesforce, etc.) are vast and constantly changing. Writing and maintaining 1‑for‑1 wrappers leads to:
- Continuous churn as SDKs update.
- A surface area far larger than most projects ever need.
- Fragile integrations that are hard to test or extend.

**Plenipotentiary flips this model:** rather than trying to expose *everything*, you selectively expose *only the parts you need* — but always in a **uniform, conventional structure** that developers can navigate by intuition.

---

## 📐 Patterns in Play

### 1. **Eloquent Model → Domain DTO**
- We don’t bind external APIs to Laravel’s Eloquent models directly.  
- Instead, we extract lightweight immutable **DTOs** (Domain Transfer Objects).  
- Why? They flatten your persistence schema into predictable PHP objects that move easily across layers and make tests simpler.

### 2. **Repository Layer**
- Repositories handle loading/saving DTOs to the local persistence store (usually Eloquent).
- They also transform DTOs back from external representations when needed.
- They free your services from caring about persistence concerns.

### 3. **Service Layer**
- Services are where **API SDK interaction happens**.  
- Split into:
  - `Generated/*Service`: Thin, raw delegates into the vendor SDK (subject to change with SDK versions).  
  - `User/*Service`: Orchestrators that validate, build requests, map exceptions, and provide stable APIs to app code.

### 4. **Request Builders & Validators**
- RequestBuilders: predictable construction of vendor SDK requests, mapped from your domain DTOs.  
- Validators: enforce local domain rules before hitting the network.  
- Both make API calls **deterministic and testable**.

### 5. **Console Commands (or Controllers)**
- Entry points that “make stuff happen.”  
- They orchestrate sync flows: fetch records, pass through services, persist back results.  
- Keeps APIs easy to invoke (CLI, scheduled jobs, or triggered by controllers).

---

## 🚫 What Plenipotentiary is *Not*
- **Not** a full SDK wrapper.  
- **Not** a replacement for vendor SDKs.  
- **Not** meant to expose every enum/endpoint Google or Xero ever add.

Instead: it’s a **minimal predictable API edge** you can roll yourself, following conventions.  

---

## ✨ Why This Matters
1. **Predictability:** Once you know the pattern (DTO → Validator → Builder → Generated → User Service → Repository → Command), any integration looks the same.  
2. **Stability:** Your User Services remain stable across SDK upgrades; only the Generated layer may change.  
3. **Flexibility:** You can swap or extend at any layer (custom validators, new repos).  
4. **Maintainability:** You’re not chasing entire API surfaces, only the few endpoints your domain needs.  
5. **Community Friendly:** Anyone can build a plugin (e.g. `plenipotentiary-laravel-googleads`, `plenipotentiary-laravel-xero`) following the same predictable conventions.  

---

## 🔮 Roadmap
Future generic features will include:
- **Queueing**: run API calls asynchronously via jobs.  
- **Retries & Backoff**: handled generically, not specific to any vendor.  
- **Logging & Monitoring**: standard logging across all integrations, plus metrics for duration, error counts, etc.  
- **Circuit Breakers**: prevent runaway failures when APIs misbehave.  

All of these fit into composition helpers (`GeneratedServiceHelpers`) so any plugin or edge code can use them.

---

### ⚖️ Summary
> Plenipotentiary is not about wrapping the world’s APIs —  
> it’s about exposing only what you need, predictably, through a repeatable pattern.  

DTOs for shape.  
Repositories for persistence.  
Services for remote IO.  
Builders + Validators for consistency.  
Console/Controllers for orchestration.  

That’s it. Roll your own API edge, but *always follow the same map*.
