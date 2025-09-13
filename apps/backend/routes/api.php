<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Plenipotentiary\Laravel\Contracts\AuthStrategy;

Route::get('/healthz', fn () => response()->json(['ok' => true]));

Route::get('/readyz', function () {
    $checks = ['db' => 'error', 'cache' => 'error'];

    try {
        DB::connection()->getPdo();
        $checks['db'] = 'ok';
    } catch (\Throwable $e) {
    }

    try {
        Cache::put('__readyz', '1', 5);
        $checks['cache'] = Cache::get('__readyz') ? 'ok' : 'error';
    } catch (\Throwable $e) {
    }

    $status = (in_array('error', $checks, true) ? 503 : 200);

    return response()->json(['status' => $status === 200 ? 'ready' : 'degraded', 'checks' => $checks], $status);
});

Route::get('/pleni-smoke', function (AuthStrategy $auth) {
    return response()->json([
        'auth_class' => $auth::class,
        'config' => config('pleni'),
    ]);
})->name('pleni.smoke');
