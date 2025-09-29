<?php

use Plenipotentiary\Laravel\Tests\Support\TestCase;

// Make all tests use Laravel Testbench base
uses(TestCase::class)->in('Feature', 'Package', 'Contracts', 'Unit');

// Global test helpers and factories
uses()
    ->beforeEach(function () {
        // Set up common test environment
        $this->app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $this->app['config']->set('cache.default', 'array');
    })
    ->in('Feature', 'Package', 'Contracts', 'Unit');
