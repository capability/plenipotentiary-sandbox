# AGENTS

| Agent | Purpose | Calls | Returns | Constraints |
|---|---|---|---|---|
| **Plenipotentiary** | Contract‑driven integration/orchestration. Runs use cases, coordinates Gateways & Repositories. | Gateways (provider‑agnostic), Repositories | InboundDTOs persisted + operation summaries | Never imports provider SDKs; DTOs are provider‑agnostic; provider specifics live only in Adapters. |