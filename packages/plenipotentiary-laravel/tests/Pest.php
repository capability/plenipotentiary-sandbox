<?php

use Plenipotentiary\Laravel\Tests\Support\TestCase;

// Make all tests in tests/Package use Laravel Testbench base
uses(TestCase::class)->in('Feature', 'Package', 'Contracts', 'Unit');
