<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Providers;

use App\Models\AcmeCart\Search\Campaign as CampaignModel;
use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Idempotency\IdempotencyHints;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignApiCrudAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateSupport\CreateBudgetOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\DeleteOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\ReadManyOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\ReadOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\UpdateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignApiCrudGateway;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\EloquentCampaignRepository;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Support\CampaignIdempotencyHints;

final class CampaignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Operations
        $this->app->singleton(CreateBudgetOperation::class);
        $this->app->singleton(CreateOperation::class);
        $this->app->singleton(UpdateOperation::class);
        $this->app->singleton(DeleteOperation::class);
        $this->app->singleton(ReadOperation::class);
        $this->app->singleton(ReadManyOperation::class);

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
