<?php

use Plenipotentiary\Laravel\Tests\Support\TestCase;
use Plenipotentiary\Laravel\Contracts\AuthStrategy;
use Plenipotentiary\Laravel\Auth\NoopAuth;

it('binds AuthStrategy to a concrete implementation', function () {
    $impl = app(AuthStrategy::class);
    expect($impl)->toBeInstanceOf(NoopAuth::class);
});