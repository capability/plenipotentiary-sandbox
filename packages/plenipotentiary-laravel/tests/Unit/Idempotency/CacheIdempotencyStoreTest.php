<?php

use Plenipotentiary\Laravel\Idempotency\CacheIdempotencyStore;

describe('Cache Idempotency Store', function () {
    beforeEach(function () {
        $this->store = new CacheIdempotencyStore(
            app('cache')->store(),
            3600
        );
    });

    it('stores and retrieves values', function () {
        $scope = 'test.scope';
        $fingerprint = 'test-fingerprint';
        $value = 'test-value';
        
        $this->store->put($scope, $fingerprint, $value);
        
        expect($this->store->get($scope, $fingerprint))->toBe($value);
    });

    it('returns null for non-existent values', function () {
        expect($this->store->get('nonexistent', 'fingerprint'))->toBeNull();
    });

    it('tombstones values', function () {
        $scope = 'test.scope';
        $fingerprint = 'test-fingerprint';
        
        $this->store->tombstone($scope, $fingerprint);
        
        expect($this->store->isTombstoned($scope, $fingerprint))->toBeTrue()
            ->and($this->store->get($scope, $fingerprint))->toBeNull();
    });

    it('handles different scopes independently', function () {
        $scope1 = 'scope1';
        $scope2 = 'scope2';
        $fingerprint = 'same-fingerprint';
        
        $this->store->put($scope1, $fingerprint, 'value1');
        $this->store->put($scope2, $fingerprint, 'value2');
        
        expect($this->store->get($scope1, $fingerprint))->toBe('value1')
            ->and($this->store->get($scope2, $fingerprint))->toBe('value2');
    });
});