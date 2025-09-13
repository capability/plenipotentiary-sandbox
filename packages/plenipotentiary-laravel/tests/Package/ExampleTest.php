<?php

it('boots the provider', function () {
    expect(config('pleni'))->toBeArray();
});
