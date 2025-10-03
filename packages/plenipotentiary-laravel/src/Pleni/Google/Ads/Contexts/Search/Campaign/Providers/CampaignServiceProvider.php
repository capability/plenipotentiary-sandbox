<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Providers;

use App\Models\AcmeCart\Search\Campaign as CampaignModel;
use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\CrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Adapter\ProcedureAdapterContract;
use Plenipotentiary\Laravel\Contracts\Adapter\RestAdapterContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ProcedureGatewayContract;
use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignCreate;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignCrudAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignDelete;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignRead;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignReadMany;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignUpdate;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateSupport\CampaignCreateBudget;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignCrudGateway;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\EloquentCampaignRepository;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Support\CampaignIdempotencyHints;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Transfer\Procedure\GoogleAdsProcedureAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Transfer\Procedure\GoogleAdsProcedureGateway;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Transfer\Rest\GoogleAdsRestAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Transfer\Rest\GoogleAdsRestGateway;

final class CampaignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Operations
        $this->app->singleton(CampaignCreateBudget::class);
        $this->app->singleton(CampaignCreate::class);
        $this->app->singleton(CampaignUpdate::class);
        $this->app->singleton(CampaignDelete::class);
        $this->app->singleton(CampaignRead::class);
        $this->app->singleton(CampaignReadMany::class);

        // Adapter / Gateway
        $this->app->singleton(CrudAdapterContract::class, CampaignCrudAdapter::class);
        $this->app->singleton(ApiCrudGatewayContract::class, CampaignCrudGateway::class);

        // Repository binding
        $this->app->bind(CampaignRepositoryContract::class, function ($app) {
            return new EloquentCampaignRepository(
                $app->make(CampaignModel::class)
            );
        });

        $this->app->when(CampaignCrudGateway::class)
            ->needs(IdempotencyHints::class)
            ->give(CampaignIdempotencyHints::class);

        // Procedure Adapter / Gateway
        $this->app->bind(ProcedureAdapterContract::class, GoogleAdsProcedureAdapter::class);
        $this->app->bind(ProcedureGatewayContract::class, GoogleAdsProcedureGateway::class);

        // REST Adapter / Gateway
        $this->app->bind(RestAdapterContract::class, GoogleAdsRestAdapter::class);
        $this->app->bind(RestGatewayContract::class, GoogleAdsRestGateway::class);
    }
}
