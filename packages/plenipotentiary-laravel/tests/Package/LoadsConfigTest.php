<?php

use Plenipotentiary\Laravel\Tests\Support\TestCase;

it('merges package config', function () {
    $config = config('pleni');
    $this->assertIsArray($config);
    expect($config)
        ->toHaveKey('observability')
        ->and($config['observability']['enabled'])->toBeFalse();
});
