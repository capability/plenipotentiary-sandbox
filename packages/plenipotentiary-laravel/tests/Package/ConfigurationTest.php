<?php

describe('Package Configuration', function () {
    it('loads pleni configuration', function () {
        expect(config('pleni'))->toBeArray()
            ->and(config('pleni.observability.enabled'))->toBeFalse()
            ->and(config('pleni.auth.default'))->toBe('noop');
    });

    it('registers service providers', function () {
        $providers = app()->getLoadedProviders();

        expect($providers)->toHaveKey(\Plenipotentiary\Laravel\Providers\PleniCoreServiceProvider::class)
            ->and($providers)->toHaveKey(\Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Providers\GoogleAdsServiceProvider::class);
    });

    it('binds PSR-3 logger interface', function () {
        expect(app()->bound(\Psr\Log\LoggerInterface::class))->toBeTrue()
            ->and(app(\Psr\Log\LoggerInterface::class))->toBeInstanceOf(\Psr\Log\LoggerInterface::class);
    });
});
