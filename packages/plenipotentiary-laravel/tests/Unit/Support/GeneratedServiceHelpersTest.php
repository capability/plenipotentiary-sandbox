<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GeneratedServiceHelpers;

it('retries until success', function () {
    $attempts = 0;

    $result = GeneratedServiceHelpers::withRetries(function () use (&$attempts) {
        $attempts++;
        if ($attempts < 2) {
            throw new RuntimeException('fail');
        }

        return 'ok';
    }, 3, 1);

    expect($result)->toBe('ok');
    expect($attempts)->toBeGreaterThan(1);
});

it('wraps operations with logging and metrics', function () {
    $result = GeneratedServiceHelpers::withMetrics('unit.test', function () {
        return GeneratedServiceHelpers::withLogging('testop', [], fn () => 42);
    });
    expect($result)->toBe(42);
});
