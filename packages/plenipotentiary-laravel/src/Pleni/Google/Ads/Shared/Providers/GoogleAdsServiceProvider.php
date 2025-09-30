<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Providers;

use App\Models\AcmeCart\Search\Campaign as CampaignModel;
use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Adapter\ApiCrudAdapterContract;
use Plenipotentiary\Laravel\Contracts\Auth\SdkAuthStrategyContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CampaignApiCrudAdapter;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\DeleteOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\UpdateOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Gateway\CampaignApiCrudGateway;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\EloquentCampaignRepository;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth\GoogleAdsSdkAuthStrategy;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Auth\GoogleAdsSdkClient;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsErrorMapper;

/**
 * Registers Google Ads specific adapters, mappers, and services.
 */
final class GoogleAdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        GoogleAdsDefaults::loadFromEnv();
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

        // Adapter operations
        $this->app->singleton(CreateOperation::class);
        $this->app->singleton(UpdateOperation::class);
        $this->app->singleton(DeleteOperation::class);

        // Gateways
        $this->app->singleton(ApiCrudGatewayContract::class, CampaignApiCrudGateway::class);

        // Domain Repositories
        $this->app->bind(CampaignRepositoryContract::class, function ($app) {
            return new EloquentCampaignRepository(
                $app->make(CampaignModel::class)
            );
        });

    }
}
