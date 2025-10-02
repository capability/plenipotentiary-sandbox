═══════════════════════════════════════════════════════════════════════════
   COMPREHENSIVE SUMMARY: Three Gateway Patterns for Different Use Cases
   ═══════════════════════════════════════════════════════════════════════════

   Your architecture now supports THREE distinct patterns, each optimized for
   different scenarios when working with third-party APIs:

   ───────────────────────────────────────────────────────────────────────────
   1. CRUD ADAPTER PATTERN (SDK or REST)
   ───────────────────────────────────────────────────────────────────────────

   ✓ Purpose: Resources with clear Create/Read/Update/Delete lifecycle
   ✓ Transport: Vendor SDK (Google Ads) OR REST API via Saloon (Stripe)
   ✓ Contract: AdapterVerbContract (same for both!)

   EXAMPLE 1: Google Ads (SDK-based)
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   CampaignApiCrudAdapter
   ├── CampaignCreate → uses Google\Ads SDK
   ├── CampaignUpdate → uses Google\Ads SDK  
   ├── CampaignDelete → uses Google\Ads SDK
   ├── CampaignRead → uses Google\Ads SDK
   └── CampaignReadMany → uses Google\Ads SDK

   Each operation:
     • Uses native SDK objects (Campaign, CampaignOperation, etc.)
     • Calls SDK methods directly ($client->getCampaignServiceClient())
     • Maps SDK responses back to CanonicalDTO

   EXAMPLE 2: Stripe (REST-based) ← NEW!
   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   CustomerApiCrudAdapter
   ├── CustomerCreateRest → uses Saloon (POST /v1/customers)
   ├── CustomerUpdateRest → uses Saloon (POST /v1/customers/:id)
   ├── CustomerDeleteRest → uses Saloon (DELETE /v1/customers/:id)
   └── CustomerReadRest → uses Saloon (GET /v1/customers/:id)

   Each operation:
     • Creates anonymous Saloon Request class in requestMapper()
     • Sends HTTP request via StripeApiRestConnector
     • Maps JSON response back to CanonicalDTO

   KEY INSIGHT: Same AdapterVerbContract, different transport!

   ───────────────────────────────────────────────────────────────────────────
   2. PROCEDURE/RPC PATTERN (Saloon-based)
   ───────────────────────────────────────────────────────────────────────────

   ✓ Purpose: Quick prototyping, ad-hoc operations, simple APIs
   ✓ Transport: HTTP via Saloon (dynamic requests)
   ✓ Contract: ProcedureAdapterContract

   Structure:
     Shared/Transfer/Procedure/
       ├── {Provider}ProcedureAdapter
       ├── {Provider}ProcedureGateway  
       ├── {Provider}ProcedureConnector
       └── {Provider}DynamicRequest

   How it works:
     1. Call with operation name + payload
     2. Adapter matches operation to endpoint (match expression)
     3. Builds dynamic Saloon request on-the-fly
     4. Sends HTTP request, returns Result

   Usage:
     $gateway->call('searchItems', ['q' => 'laptop', 'limit' => 20]);
     $gateway->call('chat.completions.create', ['model' => 'gpt-4', ...]);

   Providers implemented:
     • GoogleAdsProcedureAdapter (searchCampaigns, mutateCampaigns)
     • eBayBrowseProcedureAdapter (searchItems, getItem, etc.)
     • OpenAIApiProcedureAdapter (chat.completions.create, etc.)

   ───────────────────────────────────────────────────────────────────────────
   3. REST PATTERN (Saloon-based)
   ───────────────────────────────────────────────────────────────────────────

   ✓ Purpose: Type-safe operations, complex APIs, many endpoints
   ✓ Transport: HTTP via Saloon (dedicated request classes)
   ✓ Contract: RestGatewayContract

      Structure:
     Shared/Transfer/Rest/
       ├── {Provider}RestAdapter
       ├── {Provider}RestGateway
       └── {Provider}RestConnector
     Contexts/{Context}/Requests/
       ├── SearchCampaignsRequest.php
       ├── SearchItemsRequest.php
       └── CreateChatCompletionRequest.php

   How it works:
     1. Create dedicated Saloon Request class per endpoint
     2. Pass Request object to gateway
     3. Gateway delegates to adapter
     4. Adapter sends via connector

   Usage:
     $request = new SearchItemsRequest('laptop', limit: 20);
     $gateway->execute($request);

   Providers implemented:
     • GoogleAdsRestAdapter + GoogleAdsRestConnector
     • eBayBrowseRestAdapter + eBayBrowseRestConnector  
     • OpenAIApiRestAdapter + OpenAIApiRestConnector

   ═══════════════════════════════════════════════════════════════════════════
   DECISION MATRIX: Which Pattern to Use?
   ═══════════════════════════════════════════════════════════════════════════

   ┌────────────────────┬─────────────┬─────────────┬─────────────┐
   │ Scenario           │ CRUD (SDK)  │ CRUD (REST) │ Procedure   │ REST        │
   ├────────────────────┼─────────────┼─────────────┼─────────────┼─────────────┤
   │ Vendor SDK exists  │     ✓✓✓     │      -      │      -      │      -      │
   │ REST CRUD resource │      -      │     ✓✓✓     │      ✓      │      ✓      │
   │ Quick prototyping  │      -      │      -      │     ✓✓✓     │      ✓      │
   │ Many endpoints     │      -      │      -      │      ✓      │     ✓✓✓     │
   │ Type safety        │     ✓✓      │     ✓✓      │      ✓      │     ✓✓✓     │
   │ Maintainability    │     ✓✓✓     │     ✓✓✓     │      ✓      │     ✓✓✓     │
   │ Ease of adding     │      ✓      │      ✓      │     ✓✓✓     │      ✓✓     │
   └────────────────────┴─────────────┴─────────────┴─────────────┴─────────────┘

   ═══════════════════════════════════════════════════════════════════════════
   FILE STRUCTURE SUMMARY
   ═══════════════════════════════════════════════════════════════════════════

   Pleni/{Provider}/{Domain}/
     ├── Contexts/
     │   └── {Context}/
     │       ├── Adapter/              ← CRUD Operations (SDK or REST)
     │       │   ├── {Resource}Create[Rest].php
     │       │   ├── {Resource}Update[Rest].php
     │       │   ├── {Resource}Delete[Rest].php
     │       │   ├── {Resource}Read[Rest].php
     │       │   └── {Resource}ApiCrudAdapter.php
     │       ├── DTO/
     │       │   └── {Resource}CanonicalDTO.php
     │       ├── Selector/
     │       │   └── {Resource}Selector.php
     │       └── Requests/             ← REST Pattern (dedicated classes)
     │           ├── SearchItemsRequest.php
     │           └── CreateCompletionRequest.php
     └── Shared/
         ├── Support/
         │   ├── {Provider}Config.php
         │   └── {Provider}ErrorMapper.php
         └── Transfer/
             ├── Procedure/            ← Procedure/RPC Pattern
             │   ├── {Provider}ProcedureAdapter.php
             │   ├── {Provider}ProcedureGateway.php
             │   ├── {Provider}ProcedureConnector.php
             │   └── {Provider}DynamicRequest.php
             └── Rest/                 ← REST Pattern (infrastructure)
                 ├── {Provider}RestAdapter.php
                 ├── {Provider}RestGateway.php
                 └── {Provider}RestConnector.php

   ═══════════════════════════════════════════════════════════════════════════
   REAL WORLD EXAMPLES
   ═══════════════════════════════════════════════════════════════════════════

   Google Ads Campaigns
     Pattern: CRUD (SDK)
     Why: Google provides excellent PHP SDK with gRPC support
     Operations: CampaignCreate, CampaignUpdate, CampaignDelete, CampaignRead

   Stripe Customers ← NEW EXAMPLE!
     Pattern: CRUD (REST)
     Why: Stripe has RESTful CRUD endpoints, SDK adds unnecessary overhead
     Operations: CustomerCreateRest, CustomerUpdateRest, CustomerDeleteRest, CustomerReadRest

   eBay Item Search
     Pattern: Procedure OR REST (developer's choice)
     Why: One-off searches don't need dedicated classes (Procedure)
          OR want type safety for complex queries (REST)

   OpenAI Chat Completions
     Pattern: REST (preferred) OR Procedure
     Why: Complex payloads benefit from dedicated request classes (REST)
          OR quick scripts can use Procedure

   ═══════════════════════════════════════════════════════════════════════════
   KEY TAKEAWAYS
   ═══════════════════════════════════════════════════════════════════════════

   1. ✓ CRUD pattern works with BOTH SDK and REST (same AdapterVerbContract!)
   2. ✓ The implementation is NEVER swapped - choose once per provider
   3. ✓ Procedure pattern for velocity, REST pattern for scale
   4. ✓ All patterns share: Result, CanonicalDTO, Gateway/Adapter separation
   5. ✓ All patterns support: logging, error mapping, policy chains

   ═══════════════════════════════════════════════════════════════════════════

