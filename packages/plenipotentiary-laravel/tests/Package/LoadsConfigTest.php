<?php

it('merges package config', function () {
    $config = config('pleni');
    $this->assertIsArray($config);
    expect($config)
        ->toHaveKey('observability')
        ->and($config['observability']['enabled'])->toBeFalse();
});
