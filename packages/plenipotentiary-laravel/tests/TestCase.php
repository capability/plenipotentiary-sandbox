<?php

namespace Plenipotentiary\Laravel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [Plenipotentiary\Laravel\Providers\PleniCoreServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        // Any minimal env config for tests
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
    }
}
