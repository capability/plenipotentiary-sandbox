<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Providers;

use App\Models\AcmeCart\Search\Campaign as CampaignModel;
use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignApiCrudAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignCreate;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateSupport\CampaignCreateBudget;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignDelete;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignReadMany;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignRead;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignUpdate;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignApiCrudGateway;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\EloquentCampaignRepository;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Support\CampaignIdempotencyHints;

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
        $this->app->singleton(ApiCrudAdapterContract::class, CampaignApiCrudAdapter::class);
        $this->app->singleton(ApiCrudGatewayContract::class, CampaignApiCrudGateway::class);

        // Repository binding
        $this->app->bind(CampaignRepositoryContract::class, function ($app) {
            return new EloquentCampaignRepository(
                $app->make(CampaignModel::class)
            );
        });

        $this->app->when(CampaignApiCrudGateway::class)
            ->needs(IdempotencyHints::class)
            ->give(CampaignIdempotencyHints::class);
    }
}
