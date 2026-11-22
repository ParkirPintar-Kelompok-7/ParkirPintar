<?php

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $db = 'up';
    } catch (\Throwable $e) {
        $db = 'down';
    }

    return response()->json(
        ['status' => $db === 'up' ? 'ok' : 'error', 'database' => $db],
        $db === 'up' ? 200 : 503
    );
});

use Illuminate\Support\Facades\Route;

    Route::view('/', 'dashboard');
