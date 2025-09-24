<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignApiCrudGateway;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignApiCrudAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\EloquentCampaignRepository;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth\GoogleAdsSdkAuthStrategy;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsErrorMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth\GoogleAdsSdkClient;

use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\RequestMapper as CreateRequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\CreateResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\ResponseMapper as CreateResponseMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\UpdateRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\RequestMapper as UpdateRequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\UpdateResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\ResponseMapper as UpdateResponseMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\DeleteRequestMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\RequestMapper as DeleteRequestMapper;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\DeleteResponseMapperContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\ResponseMapper as DeleteResponseMapper;

use App\Models\AcmeCart\Search\Campaign as CampaignModel;

use Plenipotentiary\Laravel\Contracts\Adapter\SpecContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Create\Spec as CreateSpec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Update\Spec as UpdateSpec;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\Delete\Spec as DeleteSpec;

/**
 * Registers Google Ads specific adapters, mappers, and services.
 */
final class GoogleAdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Auth
        $this->app->singleton(SdkAuthStrategyContract::class, GoogleAdsSdkAuthStrategy::class);

        // Bind the unified ProviderClientContract to our GoogleAdsSdkClient wrapper
        $this->app->singleton(ProviderClientContract::class, function ($app) {
            /** @var SdkAuthStrategyContract $auth */
            $auth = $app->make(SdkAuthStrategyContract::class);

            return new GoogleAdsSdkClient(
                $auth->getClient() // raw GoogleAdsClient
            );
        });

        // Error Mapper
        $this->app->singleton(ErrorMapperContract::class, GoogleAdsErrorMapper::class);

        // Adapters
        $this->app->singleton(ApiCrudAdapterContract::class, CampaignApiCrudAdapter::class);

        // Gateways
        $this->app->singleton(ApiCrudGatewayContract::class, CampaignApiCrudGateway::class);

        // Domain Repositories
        $this->app->bind(CampaignRepositoryContract::class, function ($app) {
            return new EloquentCampaignRepository(
                $app->make(CampaignModel::class)
            );
        });

        // Mapper contracts
        $this->app->bind(CreateRequestMapperContract::class, CreateRequestMapper::class);
        $this->app->bind(CreateResponseMapperContract::class, CreateResponseMapper::class);
        $this->app->bind(UpdateRequestMapperContract::class, UpdateRequestMapper::class);
        $this->app->bind(UpdateResponseMapperContract::class, UpdateResponseMapper::class);
        $this->app->bind(DeleteRequestMapperContract::class, DeleteRequestMapper::class);
        $this->app->bind(DeleteResponseMapperContract::class, DeleteResponseMapper::class);

        // Spec contracts
        $this->app->when(CampaignApiCrudAdapter::class)
            ->needs(SpecContract::class)
            ->give(function ($app) {
                // We might want more granular binding, but for simplicity bind directly
                return new CreateSpec();
            });

        $this->app->when(CampaignApiCrudAdapter::class)
            ->needs(SpecContract::class)
            ->give(function ($app) {
                // Provide UpdateSpec if requested explicitly elsewhere
                return new UpdateSpec();
            });

        $this->app->when(CampaignApiCrudAdapter::class)
            ->needs(SpecContract::class)
            ->give(function ($app) {
                // Provide DeleteSpec if requested explicitly elsewhere
                return new DeleteSpec();
            });
    }
}
