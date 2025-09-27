# Using Plenipotentiary‑Laravel: Two Approaches                                                                                                                                                           
                                                                                                                                                                                                          
Plenipotentiary is designed to be **predictable**, but it recognises that not every app wants the same level of “out of the box wiring.”                                                                  
There are two main ways to work with the package:                                                                                                                                                         
                                                                                                                                                                                                          
---                                                                                                                                                                                                       
                                                                                                                                                                                                          
## 1. **"Just Works" Mode** (Generated Stubs)                                                                                                                                                             
                                                                                                                                                                                                          
In this mode Plenipotentiary provides you with a **scaffolded repository, model, and service provider binding** so that you can plug in and go without any extra wiring.                                  
                                                                                                                                                                                                          
- Run a generator (future: `php artisan pleni:make campaign`).                                                                                                                                            
- This creates:                                                                                                                                                                                           
  - `App\Models\Google\Ads\Contexts\Search\Campaign.php`                                                                                                                                                  
  - `App\Repositories\Google\Ads\Contexts\Search\CampaignRepositoryInterface.php`                                                                                                                         
  - `App\Repositories\Google\Ads\Contexts\Search\EloquentCampaignRepository.php`                                                                                                                          
- The `GoogleAdsServiceProvider` will **bind** the repository interface to the default Eloquent implementation automatically.                                                                             
                                                                                                                                                                                                          
📦 **Pros**                                                                                                                                                                                               
- Zero‑friction setup.                                                                                                                                                                                    
- Works immediately with the default Eloquent model convention.                                                                                                                                           
- Best for greenfield apps, POCs, or developers who want Plenipotentiary to manage persistence conventions.                                                                                               
                                                                                                                                                                                                          
⚠️ **Cons**                                                                                                                                                                                               
- Imposes directory and namespace structure (`App\Models\{provider}\{service}\{context}\{resource}`).                                                                                                     
- Less flexibility if app already has models & repositories defined.                                                                                                                                      
                                                                                                                                                                                                          
---                                                                                                                                                                                                       
                                                                                                                                                                                                          
## 2. **"Fit With Current Model Structures" Mode** (Bring Your Own Models)                                                                                                                                
                                                                                                                                                                                                          
In this mode Plenipotentiary assumes only the **core contracts** (e.g. `BaseRepositoryInterface`) exist.                                                                                                  
You, the developer, implement your own domain repositories against your own models.                                                                                                                       
                                                                                                                                                                                                          
- You create your own:                                                                                                                                                                                    
  - Model (e.g. `App\Models\Campaign` or `App\Models\Invoices\Invoice`).                                                                                                                                  
  - Repository that implements the relevant `*RepositoryInterface`.                                                                                                                                       
- Plenipotentiary does **not bind** a concrete implementation for you.                                                                                                                                    
- In your own `AppServiceProvider` (or a custom provider) you wire it up:                                                                                                                                 
                                                                                                                                                                                                          
```php                                                                                                                                                                                                    
$this->app->bind(                                                                                                                                                                                         
    \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryInterface::class,                                                                                     
    \App\Repositories\EloquentCampaignRepository::class                                                                                                                                                   
);                                                                                                                                                                                                        
                                                                                                                                                                                                          

📦 Pros                                                                                                                                                                                                   

 • Maximum flexibility: use existing models and repositories.                                                                                                                                             
 • No imposed file structure.                                                                                                                                                                             
 • Easy to adapt in legacy or complex domain projects.                                                                                                                                                    

⚠️ Cons                                                                                                                                                                                                   

 • Slightly more setup effort.                                                                                                                                                                            
 • You must supply your own implementation for each resource you want to integrate.                                                                                                                       

──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

Which Should You Choose?                                                                                                                                                                                  

 • Small/greenfield project → start with Just Works mode.                                                                                                                                                 
 • Enterprise/legacy project → use Fit With Current Model Structures mode.                                                                                                                                

Both approaches still benefit from the predictable contracts, gateway–adapter separation, and testability that Plenipotentiary provides.

──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────

Responsibilities Comparison                                                                                                                                                                               

                                                                                                                                                                         
  Responsibility                                                "Just Works" Mode (Generated)      "Fit With Current Structures" Mode (BYO Models)                            
 ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ 
  Core contracts (BaseRepositoryInterface, etc.)                ✅ Provided by Plenipotentiary                ✅ Provided by Plenipotentiary                                             
  Repository interface (e.g. CampaignRepositoryInterface)       ✅ Generated stub automatically               ❌ Developer writes their own interface extending BaseRepositoryInterface  
  Repository implementation (e.g. EloquentCampaignRepository)   ✅ Generated stub and auto‑bound                ❌ Developer implements and binds                                          
  Domain models (Eloquent)                                      ✅ Generated into App\Models\{provider}\...     ❌ Developer uses existing models or creates their own                     
  Service provider binding                                      ✅ Auto‑binds repository -> implementation      ❌ Developer must bind in AppServiceProvider or equivalent                 
  Developer setup effort                                        Minimal → “just works”                          Higher → wire your own repos/models                                        
  Flexibility                                                   Lower: you follow Pleni’s namespace structure   Higher: you integrate with existing domain structure                       
                                                                                                                                                                                           




──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
This design is intentional: Plenipotentiary gives you a default path but doesn’t force it. You can opt out when you already have your own domain conventions.    