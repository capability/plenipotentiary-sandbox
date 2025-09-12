<?php

use Illuminate\Foundation\Testing\TestCase;
use Tests\TestCase as BaseTestCase;

// This makes Laravel's TestCase the default for all Pest tests.
uses(BaseTestCase::class)->in('Feature', 'Unit');
