<?php

namespace Plenipotentiary\Laravel\Tests\Support;

use Orchestra\Testbench\TestCase as Orchestra;
use Plenipotentiary\Laravel\PleniServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [PleniServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Override any defaults for predictable tests if needed
        $app['config']->set('pleni.observability.enabled', false);
        $app['config']->set('pleni.auth.default', 'noop');
    }
}
