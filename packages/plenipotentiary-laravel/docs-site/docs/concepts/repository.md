# Repository Pattern in Plenipotentiary

The **Repository** layer in this package exists to provide a clear persistence boundary between domain logic (Gateways, DTOs, Adapters) and storage details (SQL, NoSQL, or even in‑memory).  

It helps in a few ways:

- **DTO Contracts**: repositories deal in canonical DTOs, not ORM models. This prevents leaking schema or Eloquent‑specific concerns outside the persistence layer.  
- **Consistency Boundary**: repositories define the rules for saving/loading aggregates such as Campaigns and related entities.  
- **Flexibility**: while Eloquent is the default, other implementations (e.g. `MongoCampaignRepository`, `InMemoryCampaignRepository`) already exist. This shows the persistence layer *can* be swapped.  
- **Testing**: higher‑level logic can depend only on the repository contract and swap in memory‑based implementations for fast tests.  

---

## Why we added it

- To make tests and persistence easier to reason about.  
- To keep provider adapters/gateways free of database concerns.  
- To allow non‑relational/document stores or even service‑based persistence in the future.  

---

## Do I have to use it?

🚫 **No mandate**:  
If your use case is simple and you’re happy with direct Eloquent usage, you do not need to adopt the repository.  

The repository pattern is supported, not enforced. It’s there when you:  
- Want a persistence abstraction for swapping storage engines.  
- Prefer DTO contracts over models in your service boundaries.  
- Need clear aggregate and relationship handling.  

If you don’t need those, it’s fine to stick with Eloquent directly.  

---

## Summary

Repositories in this codebase are **optional abstraction tools**:  
- They keep persistence logic consistent and swappable.  
- But there’s no requirement to use them if Eloquent alone already fits your needs.  
