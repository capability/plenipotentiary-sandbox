<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignApiCrudGateway;                                                                                                     
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryContract;                                                                                              
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Mapper\LocalCampaignToOutboundMapper;

class RunSandbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-sandbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demonstrate syncing a local Campaign (id=3) to Google Ads.';

    /**
     * Execute the console command.
     */
    public function handle(CampaignRepositoryContract $repo, CampaignApiCrudGateway $gateway)
    {
        // 1. Load local campaign from repository                                                                                                                                                         
        $local = $repo->find(3);                                                                                                                                                                          
        if (! $local) {                                                                                                                                                                                   
            $this->error('Local Campaign with id=3 not found.');                                                                                                                                          
            return Command::FAILURE;                                                                                                                                                                      
        }                                                                                                                                                                                                 
                                                                                                                                                                                                          
        // 2. Map local Eloquent model → OutboundDTO                                                                                                                                                      
        $outbound = LocalCampaignToOutboundMapper::map($local);                                                                                                                                           
                                                                                                                                                                                                          
        // 3. Create remote campaign via Gateway                                                                                                                                                          
        $inbound = $gateway->create($outbound);                                                                                                                                                           
                                                                                                                                                                                                          
        // 4. Update local model with IDs from Google Ads                                                                                                                                                 
        $raw = $inbound->getRawResponse();                                                                                                                                                                
        $repo->update($local->id, [                                                                                                                                                                       
            'resource_id'   => $raw['id'] ?? null,                                                                                                                                                        
            'resource_name' => $raw['resourceName'] ?? null,                                                                                                                                              
            'name'          => $raw['name'] ?? $local->name,                                                                                                                                              
        ]);                                                                                                                                                                                               
                                                                                                                                                                                                          
        $this->info("✅ Synced campaign ID {$local->id} to remote: {$raw['resourceName']}");                                                                                                              
        return Command::SUCCESS;   
    }
}
