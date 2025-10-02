<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Providers;

use Illuminate\Support\ServiceProvider;
use Plenipotentiary\Laravel\Contracts\Gateway\RestGatewayContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Commands\GetItemCommand;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Commands\SearchItemsCommand;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support\EbayConfig;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support\eBayErrorMapper;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Transfer\Rest\eBayBrowseRestAdapter;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Transfer\Rest\eBayBrowseRestConnector;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Transfer\Rest\eBayBrowseRestGateway;
use Psr\Log\LoggerInterface;

/**
 * Service provider for eBay Browse API integration.
 * 
 * This provider wires up all the components needed for the eBay Browse API:
 * - REST connector, adapter, and gateway
 * - Actions (automatically resolved via dependency injection)
 * - Commands for CLI usage
 * 
 * The provider demonstrates clean separation between:
 * - Infrastructure (Saloon connector)
 * - Adapter layer (eBay-specific communication)
 * - Gateway layer (stable, predictable interface)
 * - Domain layer (Actions, Commands, Controllers)
 */
class eBayBrowseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register configuration
        $this->app->singleton(EbayConfig::class, function ($app) {
            return new EbayConfig(
                accessToken: config('services.ebay.access_token'),
                marketplaceId: config('services.ebay.marketplace_id', 'EBAY_US'),
                isSandbox: config('services.ebay.sandbox', false),
            );
        });

        // Register error mapper
        $this->app->singleton(eBayErrorMapper::class);

        // Register Saloon connector
        $this->app->singleton(eBayBrowseRestConnector::class, function ($app) {
            return new eBayBrowseRestConnector(
                config: $app->make(EbayConfig::class),
            );
        });

        // Register adapter
        $this->app->singleton(eBayBrowseRestAdapter::class, function ($app) {
            return new eBayBrowseRestAdapter(
                connector: $app->make(eBayBrowseRestConnector::class),
                errorMapper: $app->make(eBayErrorMapper::class),
                logger: $app->make(LoggerInterface::class),
            );
        });

        // Register gateway with binding to interface
        // This allows Actions to depend on RestGatewayContract
        $this->app->when([
            \Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\SearchItemsAction::class,
            \Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\GetItemAction::class,
            \Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\SearchByImageAction::class,
        ])
        ->needs(RestGatewayContract::class)
        ->give(function ($app) {
            return new eBayBrowseRestGateway(
                adapter: $app->make(eBayBrowseRestAdapter::class),
                logger: $app->make(LoggerInterface::class),
            );
        });
    }

    public function boot(): void
    {
        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                SearchItemsCommand::class,
                GetItemCommand::class,
            ]);
        }
    }
}
