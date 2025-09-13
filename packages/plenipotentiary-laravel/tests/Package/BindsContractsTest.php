<?php

use Plenipotentiary\Laravel\Auth\NoopAuth;
use Plenipotentiary\Laravel\Contracts\AuthStrategy;

it('binds AuthStrategy to a concrete implementation', function () {
    $impl = app(AuthStrategy::class);
    expect($impl)->toBeInstanceOf(NoopAuth::class);
});
