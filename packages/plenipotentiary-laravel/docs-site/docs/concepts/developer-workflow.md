# Developer Workflow

Learn the SDK first, codify your contract, then let the tooling scaffold itself to your spec.  
No magic... just leverage built on understanding.

---

## 1. Auth, simply (built-in strategies)
- Use env-driven auth strategies to obtain a real, authenticated client quickly.  
- Prove calls with real credentials (sandbox/`validateOnly` when available).  
- Swap in a mocked client for unit tests.  

---

## 2. Start with the API or SDK (understanding over abstraction)
- Open one file, one operation.  
- Adapt (almost paste) the provider SDK example and make it run.  
- Map the snippet to your business/application use case and identify the **minimum data you truly need**.  
- Keep everything in one easy-to-understand place.  
- Plenipotentiary promotes **understanding, not magic or over abstraction**.  

---

## 3. Define your INPUT_SPEC
- Codify required fields as the operation's `INPUT_SPEC`.  
- This is your **explicit contract** with the API — visible, auditable, and owned by you.  

---

## 4. Stay in one place until it's green
- Keep everything in the adapter operation (auth, request build, response mapping, preflight from `INPUT_SPEC`).  
- Don’t split out code until **unit tests for `perform()` are green**:  
  - ✅ success  
  - ❌ invalid input  
  - ⚠️ mapped errors  

Passing tests mean you genuinely understand the API vs your use case.

---

## 5. Run through the Gateway — Your Domain, Predictable
- Call the Gateway. It will likely fail at first.  
- The error payload shows you the **CanonicalDTO and Factory** matching the `INPUT_SPEC`.  
- Gateway provides a stable, provider-agnostic entry to your app.  
- It normalizes results and applies:  
  - Uniform `ok / invalid / err` results  
  - Error mapping  
  - Idempotency  
  - Observability

---

## 6. Scaffold appears, to your spec
- Generate/paste the DTO and Factory.  
- They are **not guesses** — they’re derived from your spec.  

---

## 7. Robustness comes online
With the Gateway boundary in place, you gain:

- Predictable validation (from `INPUT_SPEC`).  
- Idempotency and safe retries.  
- Clean domain error mapping.  
- Safe logging/redaction.  
- Queueing, scheduling, cron integration.  

---

## ✅ Summary
- Start with the SDK → prove understanding.  
- Declare `INPUT_SPEC`.  
- Test until green.  
- Run through the Gateway.  
- Scaffold (DTO + Factory) follows your spec.  
- Deliver reliability, observability, and retries automatically.  
