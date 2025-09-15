<?php

use Illuminate\Testing\Fluent\AssertableJson;

it('returns ok on /healthz', function () {
    $this->getJson('/api/healthz')
        ->assertOk()
        ->assertJson(['ok' => true]);
});

it('returns ready on /readyz', function () {
    $this->getJson('/api/readyz')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('status', 'ready')
            ->where('checks.db', 'ok')
            ->where('checks.cache', 'ok')
            ->etc()
        );
});

// Temporary test to verify the test harness is working
// it('boots the test harness', function () {
//     expect(false)->toBeTrue(); // <- temp fail
// });
