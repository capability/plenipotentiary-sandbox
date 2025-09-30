<?php

namespace Plenipotentiary\Laravel\Tests\Support;

use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as Orchestra;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Providers\GoogleAdsServiceProvider;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Providers\PleniCoreServiceProvider;
use App\Models\AcmeCart\Search\Campaign;
use App\Models\AcmeCart\Search\AdGroup;
use App\Models\AcmeCart\Search\Ad;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PleniCoreServiceProvider::class,
            GoogleAdsServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        GoogleAdsDefaults::set('google.customerId', '1234567890');

        if (! class_exists(\App\Models\AcmeCart\Search\Campaign::class)) {
            require_once __DIR__.'/../Stubs/Models/Campaign.php';
        }
        if (! class_exists(\App\Models\AcmeCart\Search\AdGroup::class)) {
            require_once __DIR__.'/../Stubs/Models/AdGroup.php';
        }
        if (! class_exists(\App\Models\AcmeCart\Search\Ad::class)) {
            require_once __DIR__.'/../Stubs/Models/Ad.php';
        }

        $this->app->instance(\App\Models\AcmeCart\Search\Campaign::class, new \App\Models\AcmeCart\Search\Campaign);
        $this->app->instance(\App\Models\AcmeCart\Search\AdGroup::class, new \App\Models\AcmeCart\Search\AdGroup);
        $this->app->instance(\App\Models\AcmeCart\Search\Ad::class, new \App\Models\AcmeCart\Search\Ad);
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
            'providerContext' => ['google.customerId' => '1234567890'],
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
