<?php

namespace Plenipotentiary\Laravel\Tests\Support;

use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as Orchestra;
use Plenipotentiary\Laravel\Providers\PleniCoreServiceProvider;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Providers\GoogleAdsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PleniCoreServiceProvider::class,
            GoogleAdsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Test configuration
        $app['config']->set('pleni.observability.enabled', false);
        $app['config']->set('pleni.auth.default', 'noop');
        $app['config']->set('cache.default', 'array');
        
        // Google Ads test environment
        $app['config']->set('google_ads', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'refresh_token' => 'test-refresh-token',
            'developer_token' => 'test-developer-token',
            'login_customer_id' => '1234567890',
        ]);
    }

    protected function defineRoutes($router): void
    {
        /** @var Router $router */
        $router->get('/', fn () => response()->noContent());
    }

    /**
     * Create a mock Google Ads client for testing
     */
    protected function mockGoogleAdsClient(): \Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient
    {
        return Mockery::mock(\Google\Ads\GoogleAds\Lib\V21\GoogleAdsClient::class);
    }

    /**
     * Create a test CampaignCanonicalDTO
     */
    protected function createTestCampaignDTO(array $overrides = []): \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO
    {
        $defaults = [
            'accountKeys' => ['google.customerId' => '1234567890'],
            'name' => 'Test Campaign',
            'status' => 'ENABLED',
            'budgetResourceName' => 'customers/1234567890/campaignBudgets/123',
            'cpcBidMicros' => 1000000,
        ];

        return \Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO::fromArray(
            array_merge($defaults, $overrides)
        );
    }
}