What we have been doing                                                                                                                                          

 • Goal: Refactor Google Ads → Search → Campaign into a clean Contexts/ structure with predictable DTOs, contracts, and traits.                                  
 • Contracts:                                                                                                                                                    
    • ApiCrudServiceContract → for CRUD services (create, read, update, delete, plus list/search-by-criteria).                                                   
    • SearchServiceContract → for external read-only/search/browse style APIs (like eBay Browse).                                                                
    • RepositoryContract → for domain persistence/repositories (e.g. Eloquent).                                                                                  
 • DTOs:                                                                                                                                                         
    • Flattened to CampaignDomainDTO.php and CampaignExternalDTO.php directly under Contexts/Search/Campaign/DTO/.                                               
    • Both include customerId directly.                                                                                                                          
 • Repository:                                                                                                                                                   
    • CampaignRepository under Contexts/Search/Campaign/Repository/.                                                                                             
    • Implements RepositoryContract. Talks to Eloquent (App\Models\Search\Campaign). Always inputs/outputs DTOs.                                                 
 • Service:                                                                                                                                                      
    • CampaignService under Contexts/Search/Campaign/Services/.                                                                                                  
    • Implements ApiCrudServiceContract.                                                                                                                         
    • Delegates heavy CRUD operations to traits. Handles exception mapping.                                                                                      
    • Exposes create(), read(), update(), delete(), listAll(), and searchByCriteria().                                                                           
 • Browse/Search Service:                                                                                                                                        
    • CampaignBrowseService → Implements SearchServiceContract for query/lookup style operations.                                                                
 • Traits (“heavy”):                                                                                                                                             
    • CreatesCampaign → direct “create” API call.                                                                                                                
    • ReadsCampaign → “read” logic via API call.                                                                                                                 
    • UpdatesCampaign → “update” logic via API call.                                                                                                             
    • DeletesCampaign → “delete” logic via API call.                                                                                                             
    • These traits encapsulate all raw interaction with the Google Ads SDK. They inline what used to be in Support/* or Translate/*.                             
 • Old cruft removed/targeted for removal:                                                                                                                       
    • Service/User and Service/Generated classes.                                                                                                                
    • Repository/Generated.                                                                                                                                      
    • Support/* helpers like CampaignRequestBuilder, CampaignValidator, CampaignExternalToDomainMapper. Their logic is pulled into traits.                       

─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────
End result (for Campaign)                                                                                                                                        

 • Contexts/Search/Campaign/DTO/CampaignDomainDTO.php                                                                                                            
 • Contexts/Search/Campaign/DTO/CampaignExternalDTO.php                                                                                                          
 • Contexts/Search/Campaign/Repository/CampaignRepository.php                                                                                                    
 • Contexts/Search/Campaign/Services/CampaignService.php                                                                                                         
 • Contexts/Search/Campaign/Services/CampaignBrowseService.php                                                                                                   
 • Contexts/Search/Campaign/Traits/{Creates,Reads,Updates,Deletes}Campaign.php                                                                                   
 • Contracts in src/Contracts/: ApiCrudServiceContract, SearchServiceContract, RepositoryContract.     