<?php

use Plenipotentiary\Laravel\Auth\NoopAuthStrategy;
use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;

it('binds AuthStrategyContract to a concrete implementation', function () {
    $impl = app(AuthStrategyContract::class);
    expect($impl)->toBeInstanceOf(NoopAuthStrategy::class);
});
