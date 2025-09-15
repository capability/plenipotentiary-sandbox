<?php

use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Support\GoogleAdsUtil;

it('converts amount to micros and back correctly', function () {
    $amount = 123.45;
    $micros = GoogleAdsUtil::toMicros($amount);
    expect($micros)->toBe(123450000);

    $restored = GoogleAdsUtil::fromMicros($micros);
    expect($restored)->toEqual($amount);
});
