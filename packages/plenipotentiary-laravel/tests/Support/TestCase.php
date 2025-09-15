<?php

namespace Plenipotentiary\Laravel\Tests\Support;

use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as Orchestra;
use Plenipotentiary\Laravel\PleniServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [PleniServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Config only
        $app['config']->set('pleni.observability.enabled', false);
        $app['config']->set('pleni.auth.default', 'noop');
    }

    /** Register test-only routes here */
    protected function defineRoutes($router): void
    {
        /** @var Router $router */
        $router->get('/', fn () => response()->noContent());
    }
}
